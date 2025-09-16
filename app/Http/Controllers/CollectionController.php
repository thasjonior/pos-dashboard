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
        Log::info('Incoming request data:', $request->all());
        
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
        // Parse client information from clientName (if provided)
        $clientName = $request->input('clientName', '');
        $clientPhone = $request->input('clientPhone', '');
        $clientInfo = $this->parseClientInfo($clientName);
        
        // Extract machine ID from metadata or try to find by collector
        $machineId = $this->extractMachineId($request);
        
        // Validate sync data - client info is now optional
        $validation = Validator::make($request->all(), [
            'id' => 'required|string',
            'receiptNumber' => 'required|string',
            'clientName' => 'nullable|string|max:500',
            'clientPhone' => 'nullable|string|max:255',
            'totalAmount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.sourceName' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'createdAt' => 'required|string',
        ]);

        if ($validation->fails()) {
            return $this->sendError($validation->errors()->first(), 422);
        }

        // Check if collection already exists (prevent duplicates)
        $existingCollection = Collection::where('receipt_id', $request->receiptNumber)
            ->orWhere('notes', 'like', '%' . $request->id . '%')
            ->first();
        
        if ($existingCollection) {
            return $this->sendResponse(new CollectionResource($existingCollection), 'Collection already exists');
        }

        try {
            // Create or get client if client information is provided
            $client = null;
            $clientDisplayName = 'Walk-in Customer';
            
            if (!empty($clientName) || !empty($clientPhone)) {
                $client = $this->getOrCreateClientFromSync($clientInfo, $clientPhone);
                if ($client) {
                    $clientDisplayName = $client->name;
                }
            }

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
            foreach ($request->items as $item) {
                // Try to find existing collection type or create new one
                $collectionType = $this->getOrCreateCollectionType($item['sourceName']);
                
                $collection->collectionItems()->create([
                    'collection_type_id' => $collectionType->id,
                    'collection_id' => $collection->id,
                    'amount' => $item['amount'],
                ]);
            }

            return $this->sendResponse(new CollectionResource($collection), 'Collection synced successfully');

        } catch (\Exception $e) {
            return $this->sendError('Failed to create collection: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle regular API request (existing logic)
     */
    private function handleRegularApiRequest(Request $request)
    {
        // Updated validation - client info is now optional
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
            return $this->sendError($validation->errors()->first(), 422);
        }

        // Handle client creation only if client information is provided
        $client = null;
        $clientDisplayName = 'Walk-in Customer';
        
        if ($request->filled('client_name') || $request->filled('client_phone')) {
            $client = BaseService::getOrCreateClient($request);
            if (!$client) {
                return $this->sendError('Client creation failed', 500);
            }
            $clientDisplayName = $client->name;
        }

        // Save collection
        $collection = Collection::create([
            'receipt_id' => $request->receipt_id,
            'client_id' => $client ? $client->id : null,
            'date' => $request->date,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'machine_id' => $request->machine_id,
            'client_name' => $clientDisplayName,
        ]);

        // Add collection items
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
     * Parse client information from clientName string
     * Format: "Name, Location, Other details"
     */
    private function parseClientInfo(string $clientName): array
    {
        if (empty($clientName)) {
            return [
                'name' => 'Walk-in Customer',
                'address' => null,
                'full_description' => null,
                'other_details' => null
            ];
        }
        
        $parts = array_map('trim', explode(',', $clientName));
        
        return [
            'name' => $parts[0] ?? 'Walk-in Customer',
            'address' => $parts[1] ?? null,
            'full_description' => $clientName,
            'other_details' => implode(', ', array_slice($parts, 2)) ?: null
        ];
    }

    /**
     * Get or create client from sync data
     */
    private function getOrCreateClientFromSync(array $clientInfo, string $phone): ?Client
    {
        try {
            // If no meaningful client info provided, return null
            if (empty($clientInfo['name']) || $clientInfo['name'] === 'Walk-in Customer') {
                if (empty($phone)) {
                    return null;
                }
            }

            // Try to find existing client by phone and name
            $query = Client::query();
            
            if (!empty($phone)) {
                $query->where('phone', $phone);
            }
            
            if (!empty($clientInfo['name']) && $clientInfo['name'] !== 'Walk-in Customer') {
                $query->where('name', $clientInfo['name']);
            }
            
            $client = $query->first();

            if (!$client && (!empty($clientInfo['name']) || !empty($phone))) {
                // Create new client only if we have meaningful information
                $client = Client::create([
                    'name' => $clientInfo['name'] ?: 'Walk-in Customer',
                    'phone' => $phone ?: null,
                    'address' => $clientInfo['address'],
                    'description' => $clientInfo['full_description'],
                ]);
            } elseif ($client) {
                // Update existing client with latest information
                $client->update([
                    'address' => $clientInfo['address'] ?? $client->address,
                    'description' => $clientInfo['full_description'] ?? $client->description,
                ]);
            }

            return $client;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract machine ID from request
     */
    private function extractMachineId(Request $request): ?int
    {
        // First check if machine_id is directly provided
        if ($request->has('machine_id')) {
            return $request->machine_id;
        }

        // Try to extract from metadata
        $metadata = $request->input('metadata', []);
        if (isset($metadata['collectorId'])) {
            // Try to find machine by collector ID or username
            $collectorUsername = $metadata['collectorUsername'] ?? null;
            if ($collectorUsername) {
                // You might want to add logic here to map collector username to machine
                // For now, we'll try to find a machine with a matching name or return null
                $machine = \App\Models\Machine::where('name', 'like', '%' . $collectorUsername . '%')->first();
                return $machine ? $machine->id : null;
            }
        }

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
        if ($request->has('cashierName')) {
            $notes[] = "Cashier: " . $request->cashierName;
        }
        
        // Add metadata information
        $metadata = $request->input('metadata', []);
        if (!empty($metadata)) {
            if (isset($metadata['collectorUsername'])) {
                $notes[] = "Collector: " . $metadata['collectorUsername'];
            }
            if (isset($metadata['loginMode'])) {
                $notes[] = "Collection Mode: " . $metadata['loginMode'];
            }
        }
        
        // Add print information
        if ($request->has('printedAt')) {
            $notes[] = "Printed at: " . $request->printedAt;
        }

        // Add note if no client info provided
        if (empty($request->clientName) && empty($request->clientPhone)) {
            $notes[] = "Walk-in customer (no client details provided)";
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
        }
        
        return $collectionType;
    }
}