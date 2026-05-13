<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Services\BaseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use App\Http\Resources\CollectionResource;
use App\Models\Client;
use App\Models\Company;
use App\Models\Machine;

class CollectionController extends BaseController
{
    //get all collections
    public function index(Request $request)
    {
        $collections = Collection::with(['client', 'machine', 'collectionItems.collectionType'])
            ->when($request->has('machine_id'), function ($query) use ($request) {
                return $query->where('machine_id', $request->machine_id);
            })
            ->when($request->has('client_id'), function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->has('from_date'), function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->from_date);
            })
            ->when($request->has('to_date'), function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->to_date);
            })
            ->when($request->has('company_id'), function ($query) use ($request) {
                $machineIds = BaseService::getMachineIdsByCompanyId($request->company_id);
                if ($machineIds) {
                    return $query->whereIn('machine_id', $machineIds);
                }
            })
            ->latest('created_at')
        ->get();
        return $this->sendResponse(CollectionResource::collection($collections), 'Collections retrieved successfully');
    }

    //create a new collection
    public function store(Request $request)
    {
        // Log::info('Incoming request data:', $request->all());
        
        // Determine if this is sync data from collector app or regular API request
        $isSyncData = $this->isSyncDataFromCollector($request);
        
        if ($isSyncData) {
            return $this->handleCollectorSyncData($request);
        } else {
            return $this->handleRegularApiRequest($request);
        }
    }

    /**
     * Check if the incoming data is from collector app sync
     */
    private function isSyncDataFromCollector(Request $request): bool
    {
        // Check for collector-specific fields
        return $request->has(['id', 'receiptNumber', 'totalAmount', 'createdAt'])
               || $request->has('metadata.collectorId');
    }

    /**
     * Handle sync data from collector app
     */
    private function handleCollectorSyncData(Request $request)
    {
        // Log::info('Processing collector sync data', [
        //     'receipt_number' => $request->receiptNumber,
        //     'client_name' => $request->input('clientName'),
        //     'client_phone' => $request->input('clientPhone'),
        //     'collector_username' => $request->input('metadata.collectorUsername'),
        //     'total_amount' => $request->totalAmount
        // ]);

        // Extract machine ID from metadata or try to find by collector
        $machineId = $this->extractMachineId($request);
        
        // Validate sync data
        $validation = Validator::make($request->all(), [
            'id' => 'required|string',
            'receiptNumber' => 'required|string',
            'totalAmount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.sourceName' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'createdAt' => 'required|string',
        ]);

        if ($validation->fails()) {
            // Log::error('Validation failed for collector sync data', [
            //     'errors' => $validation->errors()->toArray(),
            //     'receipt_number' => $request->receiptNumber
            // ]);
            return $this->sendError($validation->errors()->first(), 422);
        }

        // Check if collection already exists (prevent duplicates)
        $existingCollection = Collection::where('receipt_id', $request->receiptNumber)
            ->orWhere(function($query) use ($request) {
                $query->where('notes', 'like', '%' . $request->id . '%')
                      ->orWhere('notes', 'like', '%' . $request->receiptNumber . '%');
            })
            ->first();
        
        if ($existingCollection) {
            // Log::info('Collection already exists', [
            //     'existing_id' => $existingCollection->id,
            //     'receipt_number' => $request->receiptNumber
            // ]);
            return $this->sendResponse(new CollectionResource($existingCollection), 'Collection already exists');
        }

        try {
            // Determine client assignment using new logic
            $client = $this->determineClientForCollection(
                $request->input('clientName'), 
                $request->input('clientPhone')
            );
            $clientDisplayName = $client ? $client->name : 'Walk-in Customer';

            // Parse date from createdAt
            $collectionDate = $this->parseCollectionDate($request->createdAt);

            // Save collection
            $collection = Collection::create([
                'receipt_id' => $request->receiptNumber,
                'client_id' => $client ? $client->id : null,
                'date' => $collectionDate,
                'amount' => $request->totalAmount,
                'notes' => $this->buildNotesFromSyncData($request),
                'machine_id' => $machineId,
                'client_name' => $clientDisplayName,
            ]);

            // Add collection items
            foreach ($request->items as $index => $item) {
                try {
                    $collectionType = $this->getOrCreateCollectionType($item['sourceName']);
                    
                    $collection->collectionItems()->create([
                        'collection_type_id' => $collectionType->id,
                        'collection_id' => $collection->id,
                        'amount' => $item['amount'],
                    ]);
                    
                } catch (\Exception $e) {
                    // Log::error('Failed to create collection item', [
                    //     'collection_id' => $collection->id,
                    //     'item_index' => $index,
                    //     'item' => $item,
                    //     'error' => $e->getMessage()
                    // ]);
                    throw $e; // Re-throw to trigger rollback
                }
            }

            // Log::info('Collection synced successfully', [
            //     'collection_id' => $collection->id,
            //     'client_id' => $client ? $client->id : null,
            //     'client_name' => $clientDisplayName,
            //     'machine_id' => $machineId,
            //     'assignment_method' => $this->getClientAssignmentMethod(
            //         $request->input('clientName'), 
            //         $request->input('clientPhone')
            //     )
            // ]);

            return $this->sendResponse(new CollectionResource($collection), 'Collection synced successfully');

        } catch (\Exception $e) {
            // Log::error('Failed to create collection from sync data', [
            //     'error' => $e->getMessage(),
            //     'receipt_number' => $request->receiptNumber,
            //     'trace' => $e->getTraceAsString()
            // ]);
            return $this->sendError('Failed to create collection: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle regular API request (existing logic preserved)
     */
    private function handleRegularApiRequest(Request $request)
    {
        // Validation - kept exactly as original
        $validation = Validator::make($request->all(), [
            'receipt_id' => 'required|string',
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:255',
            'machine_id' => 'nullable|integer|exists:machines,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.type_id' => 'required|integer|exists:collection_types,id',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        if ($validation->fails()) {
            // Log::error("ERROR VALIDATION".$validation->errors()->first());
            return $this->sendError($validation->errors()->first(), 422);
        }

        // Handle client creation - preserved original logic with slight enhancement
        $client = null;
        $clientDisplayName = 'Walk-in Customer';
        
        if ($request->filled('client_name') || $request->filled('client_phone')) {
            $client = BaseService::getOrCreateClient($request);
            if (!$client) {
                return $this->sendError('Client creation failed', 500);
            }
            $clientDisplayName = $client->name;
        } else {
            // Use default client based on authenticated user (if applicable)
            $defaultClient = $this->getDefaultClientForCollector();
            if ($defaultClient) {
                $client = $defaultClient;
                $clientDisplayName = $client->name;
            }
        }

        // Save collection - preserved original structure
        $collection = Collection::create([
            'receipt_id' => $request->receipt_id,
            'client_id' => $client ? $client->id : null,
            'date' => $request->date,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'machine_id' => $request->machine_id,
            'client_name' => $clientDisplayName,
        ]);

        // Add collection items - preserved original logic
        foreach ($request->items as $item) {
            $collection->collectionItems()->create([
                'collection_type_id' => $item['type_id'],
                'collection_id' => $collection->id,
                'amount' => $item['amount'],
            ]);
        }

        return $this->sendResponse(new CollectionResource($collection), 'Collection created successfully');
    }

    /**
     * NEW: Determine which client should be assigned to the collection
     */
    private function determineClientForCollection(?string $clientName, ?string $clientPhone): ?Client
    {
        // Check if we have meaningful client information
        $hasValidClientName = !empty($clientName) && 
                             !in_array(strtolower(trim($clientName)), ['unknown', '']);
        
        $hasValidClientPhone = !empty($clientPhone) && 
                              trim($clientPhone) !== '';

        // Log::debug('Determining client assignment', [
        //     'client_name' => $clientName,
        //     'client_phone' => $clientPhone,
        //     'has_valid_name' => $hasValidClientName,
        //     'has_valid_phone' => $hasValidClientPhone
        // ]);

        // Case 1: Valid client information provided - create/get specific client
        if ($hasValidClientName || $hasValidClientPhone) {
            $clientInfo = $this->parseClientInfo($clientName);
            $client = $this->getOrCreateClientFromSync($clientInfo, $clientPhone);
            
            if ($client) {
                // Log::debug('Using specific client', ['client_id' => $client->id, 'client_name' => $client->name]);
                return $client;
            }
        }

        // Case 2: No valid client info - use default client based on collector
        // Log::debug('No valid client info provided, using default collector client');
        $defaultClient = $this->getDefaultClientForCollector();
        
        if ($defaultClient) {
            Log::debug('Assigned default client', [
                'client_id' => $defaultClient->id, 
                'client_name' => $defaultClient->name
            ]);
            return $defaultClient;
        }

        // Case 3: No default client found - return null (will use 'Walk-in Customer')
        // Log::debug('No default client could be determined');
        return null;
    }

    /**
     * Parse client information from clientName string - Enhanced
     * Format: "Name, Location, Other details"
     */
    private function parseClientInfo(?string $clientName): array
    {
        // Treat empty, null, or "unknown" as no client info
        if (empty($clientName) || 
            in_array(strtolower(trim($clientName)), ['unknown', ''])) {
            return [
                'name' => null,
                'address' => null,
                'full_description' => null,
                'other_details' => null
            ];
        }
        
        $parts = array_map('trim', explode(',', $clientName));
        
        return [
            'name' => $parts[0] ?? null,
            'address' => $parts[1] ?? null,
            'full_description' => $clientName,
            'other_details' => implode(', ', array_slice($parts, 2)) ?: null
        ];
    }

    /**
     * Get or create client from sync data - Enhanced
     */
    private function getOrCreateClientFromSync(array $clientInfo, ?string $phone): ?Client
    {
        try {
            // If no meaningful client info provided, return null
            if (empty($clientInfo['name']) && empty($phone)) {
                return null;
            }

            // Try to find existing client by phone and/or name
            $query = Client::query();
            
            if (!empty($phone)) {
                $query->where('phone', $phone);
            }
            
            if (!empty($clientInfo['name'])) {
                if (!empty($phone)) {
                    $query->orWhere('name', $clientInfo['name']);
                } else {
                    $query->where('name', $clientInfo['name']);
                }
            }
            
            $client = $query->first();

            if (!$client && (!empty($clientInfo['name']) || !empty($phone))) {
                // Create new client only if we have meaningful information
                $client = Client::create([
                    'name' => $clientInfo['name'] ?: ('Client-' . substr($phone ?? '0000', -4)),
                    'phone' => $phone ?: null,
                    'address' => $clientInfo['address'],
                    'description' => $clientInfo['full_description'],
                ]);
                
                // Log::info('Created new client from sync', [
                //     'client_id' => $client->id,
                //     'name' => $client->name,
                //     'phone' => $client->phone
                // ]);
            } elseif ($client) {
                // Update existing client with latest information if provided
                $updateData = [];
                if (!empty($clientInfo['address'])) $updateData['address'] = $clientInfo['address'];
                if (!empty($clientInfo['full_description'])) $updateData['description'] = $clientInfo['full_description'];
                
                if (!empty($updateData)) {
                    $client->update($updateData);
                    // Log::debug('Updated existing client', ['client_id' => $client->id]);
                }
            }

            return $client;
        } catch (\Exception $e) {
            // Log::error('Error creating/updating client from sync', [
            //     'error' => $e->getMessage(),
            //     'client_info' => $clientInfo,
            //     'phone' => $phone
            // ]);
            return null;
        }
    }

    /**
     * Extract machine ID from request.
     *
     * Resolution order:
     *  1. machine_id provided directly in the request.
     *  2. Exact Machine name match on metadata.collectorUsername.
     *  3. Authenticated user's own machine via machine_name.
     */
    private function extractMachineId(Request $request): ?int
    {
        if ($request->has('machine_id') && !empty($request->machine_id)) {
            Log::debug('Machine resolved via request machine_id', ['machine_id' => $request->machine_id]);
            return $request->machine_id;
        }

        $collectorUsername = $request->input('metadata.collectorUsername');

        if ($collectorUsername) {
            $machine = Machine::where('name', $collectorUsername)->first();
            if ($machine) {
                Log::debug('Machine resolved via exact collectorUsername match', [
                    'machine_id' => $machine->id,
                    'collector_username' => $collectorUsername,
                ]);
                return $machine->id;
            }
        }

        $user = auth()->user();
        if ($user && !empty($user->machine_name)) {
            $machine = Machine::where('name', $user->machine_name)->first();
            if ($machine) {
                Log::debug('Machine resolved via authenticated user machine_name', [
                    'machine_id' => $machine->id,
                    'machine_name' => $user->machine_name,
                ]);
                return $machine->id;
            }
        }

        Log::warning('Could not resolve machine_id', [
            'collector_username' => $collectorUsername ?? 'not_provided',
            'authenticated_user' => $user?->name ?? 'not_authenticated',
        ]);

        return null;
    }

    /**
     * Parse collection date from createdAt string
     */
    private function parseCollectionDate(string $createdAt): string
    {
        try {
            $date = \Carbon\Carbon::parse($createdAt);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // Log::warning('Failed to parse collection date, using current date', [
            //     'createdAt' => $createdAt,
            //     'error' => $e->getMessage()
            // ]);
            return now()->format('Y-m-d');
        }
    }

    /**
     * Build notes from sync data
     */
    private function buildNotesFromSyncData(Request $request): string
    {
        $notes = [];
        
        // Add sync identifier
        $notes[] = "Synced from collector app";
        $notes[] = "Original ID: " . $request->id;
        $notes[] = "Receipt Number: " . $request->receiptNumber;
        
        // Add cashier information
        if ($request->has('cashierName') && !empty($request->cashierName)) {
            $notes[] = "Cashier: " . $request->cashierName;
        }
        
        // Add metadata information
        $metadata = $request->input('metadata', []);
        if (!empty($metadata)) {
            if (isset($metadata['collectorUsername']) && !empty($metadata['collectorUsername'])) {
                $notes[] = "Collector: " . $metadata['collectorUsername'];
            }
            if (isset($metadata['loginMode']) && !empty($metadata['loginMode'])) {
                $notes[] = "Collection Mode: " . $metadata['loginMode'];
            }
        }
        
        // Add print information
        if ($request->has('printedAt') && !empty($request->printedAt)) {
            $notes[] = "Printed at: " . $request->printedAt;
        }

        // Add note about client assignment
        $clientName = $request->input('clientName');
        $clientPhone = $request->input('clientPhone');
        if (empty($clientName) || strtolower(trim($clientName)) === 'unknown') {
            if (empty($clientPhone)) {
                $notes[] = "Walk-in customer (no client details provided)";
            }
        }
        
        return implode(' | ', $notes);
    }

    /**
     * Get or create collection type
     */
    private function getOrCreateCollectionType(string $sourceName): \App\Models\CollectionType
    {
        $collectionType = \App\Models\CollectionType::where('name', $sourceName)->first();
        
        if (!$collectionType) {
            $collectionType = \App\Models\CollectionType::create([
                'name' => $sourceName,
                'description' => 'Auto-created from collector app sync',
                'is_active' => true,
            ]);
            
            // Log::info('Created new collection type', [
            //     'type_id' => $collectionType->id,
            //     'name' => $sourceName
            // ]);
        }
        
        return $collectionType;
    }

    /**
     * Resolve the fallback client for a company's collectors.
     *
     * Uses the company slug to derive the client name, so any future company
     * gets its fallback auto-created on first sync without code changes.
     */
    private function resolveUnknownClientForCompany(Company $company): Client
    {
        $fallbackName = "unknown-client-{$company->slug}";

        return Client::firstOrCreate(
            ['name' => $fallbackName],
            [
                'phone'       => null,
                'address'     => "Default client for {$company->name} collectors",
                'description' => "Auto-generated default client for {$company->name} collector operations",
            ]
        );
    }

    /**
     * Get default client for the authenticated collector via machine → company → fallback.
     */
    private function getDefaultClientForCollector(): ?Client
    {
        $user = auth()->user();

        if (!$user || empty($user->machine_name)) {
            return null;
        }

        $machine = Machine::where('name', $user->machine_name)->with('company')->first();

        if (!$machine || !$machine->company) {
            Log::debug('No machine/company found for default client resolution', [
                'machine_name' => $user->machine_name,
            ]);
            return null;
        }

        $client = $this->resolveUnknownClientForCompany($machine->company);

        Log::debug('Resolved default client', [
            'client_id'    => $client->id,
            'client_name'  => $client->name,
            'company_slug' => $machine->company->slug,
        ]);

        return $client;
    }

    /**
     * Determine how the client was assigned (for logging/debugging).
     */
    private function getClientAssignmentMethod(?string $clientName, ?string $clientPhone): string
    {
        $hasValidClientName  = !empty($clientName) && !in_array(strtolower(trim($clientName)), ['unknown', '']);
        $hasValidClientPhone = !empty($clientPhone);

        if ($hasValidClientName || $hasValidClientPhone) {
            return 'specific_client';
        }

        $user = auth()->user();
        if ($user && !empty($user->machine_name)) {
            $hasMachine = Machine::where('name', $user->machine_name)->exists();
            if ($hasMachine) {
                return 'default_collector_client';
            }
        }

        return 'walk_in_customer';
    }
}