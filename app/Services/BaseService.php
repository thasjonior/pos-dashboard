<?php
// app/Services/GeneralService.php

namespace App\Services;

use App\Models\User;
use App\Models\Client;
use App\Models\Role;
use App\Models\Machine;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Http\Request;
use App\Models\Collection;
use Carbon\Carbon;
use App\Models\CollectionItem;

class BaseService
{
    /**
     * Get or create a collector user
     *
     * @param array $collectorData
     * @return User
     * @throws Exception
     */
    public function getOrCreateCollector(array $collectorData): User
    {
        try {
            DB::beginTransaction();

            // Validate required fields
            // $this->validateCollectorData($collectorData);

            // Check if collector exists by email or phone
            $collector = $this->findExistingCollector($collectorData);

            if ($collector) {
                // Update existing collector if needed
                $collector = $this->updateCollectorIfNeeded($collector, $collectorData);
                DB::commit();
                return $collector;
            }

            // Create new collector
            $collector = $this->createNewCollector($collectorData);

            DB::commit();
            return $collector;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to get or create collector: " . $e->getMessage());
        }
    }


    //getCreateOrUpdateMachine
    //find existing Machine

   
    /**
     * Get or create both collector and client in one transaction
     *
     * @param array $collectorData
     * @param array $clientData
     * @param string $machineId
     * @return array ['collector' => User, 'client' => Client]
     * @throws Exception
     */
  
    // }

    /**
     * Find clients by various search criteria
     *
     * @param array $searchCriteria
     * @return \Illuminate\Database\Eloquent\Collection
     */
   
    /**
     * Find collectors by various search criteria
     *
     * @param array $searchCriteria
     * @return \Illuminate\Database\Eloquent\Collection
     */
    
    /**
     * Get client statistics
     *
     * @param string $clientId
     * @return array
     */
  
    /**
     * Get collector statistics
     *
     * @param string $collectorId
     * @return array
     */
 
    /**
     * Bulk create or update clients
     *
     * @param array $clientsData
     * @param string $machineId
     * @return array
     */
    

    /**
     * Find existing collector
     */
    private function findExistingCollector(array $data): ?User
    {
        $query = User::where('role','collector');  

        // Search by phone first (most specific)
        if (!empty($data['collector_name'])) {
            $collector = $query->where('name', $data['collector_name'])->first();
            if ($collector) return $collector;
        }

        // Search by phone if email not found
        if (!empty($data['collector_phone'])) {
            $phone = $this->formatPhoneNumber($data['collector_phone']);
            return $query->where('phone', $phone)->first();
        }

        return null;
    }

 

    /**
     * Create new collector
     */
    private function createNewCollector(array $data): User
    {
        
        

        return User::create([
            'id' => Str::uuid(),
            'name' => $data['collector_name'],
            'phone' => !empty($data['collector_phone']) ? $this->formatPhoneNumber($data['collector_phone']) : null,
            'password' => Hash::make($data['password'] ?? '1234'),
            'machine_name' => $data['machine_name'],
            'role' => 'collector',
        ]);
    }

   

    /**
     * Update collector if needed
     */
    private function updateCollectorIfNeeded(User $collector, array $data): User
    {
        $needsUpdate = false;
        $updates = [];

        // Check if name needs update
        if (!empty($data['collector_name']) && $collector->name !== $data['collector_name']) {
            $updates['name'] = $data['collector_name'];
            $needsUpdate = true;
        }

        // Check if phone needs update
        if (!empty($data['collector_phone'])) {
            $formattedPhone = $this->formatPhoneNumber($data['collector_phone']);
            if ($collector->phone !== $formattedPhone) {
                $updates['phone'] = $formattedPhone;
                $needsUpdate = true;
            }
        }


        if ($needsUpdate) {
            $collector->update($updates);
            $collector->refresh();
        }

        return $collector;
    }

  

    /**
     * Format phone number to standard format
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Tanzanian phone numbers
        if (str_starts_with($cleaned, '255')) {
            return '+' . $cleaned;
        } elseif (str_starts_with($cleaned, '0')) {
            return '+255' . substr($cleaned, 1);
        } elseif (strlen($cleaned) === 9) {
            return '+255' . $cleaned;
        }
        
        // Return with + if not already present
        return str_starts_with($phone, '+') ? $phone : '+' . $cleaned;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber(string $phone): bool
    {
        $formatted = $this->formatPhoneNumber($phone);
        
        // Tanzanian phone number validation
        return preg_match('/^\+255[67]\d{8}$/', $formatted) === 1;
    }

    /**
     * Calculate payment frequency for a client
     */
    private function calculatePaymentFrequency($collections): string
    {
        if ($collections->count() < 2) {
            return 'Insufficient data';
        }

        $sortedDates = $collections->pluck('date')->sort();
        $intervals = [];

        for ($i = 1; $i < $sortedDates->count(); $i++) {
            $intervals[] = $sortedDates[$i]->diffInDays($sortedDates[$i - 1]);
        }

        $averageInterval = array_sum($intervals) / count($intervals);

        if ($averageInterval <= 7) return 'Weekly';
        if ($averageInterval <= 31) return 'Monthly';
        if ($averageInterval <= 93) return 'Quarterly';
        if ($averageInterval <= 186) return 'Semi-annually';
        if ($averageInterval <= 366) return 'Annually';

        return 'Irregular';
    }

    /**
     * Get preferred payment types for a client
     */
    private function getPreferredPaymentTypes($collections): array
    {
        $types = [];
        
        foreach ($collections as $collection) {
            foreach ($collection->collectionItems as $item) {
                $typeName = $item->collectionType->name;
                $types[$typeName] = ($types[$typeName] ?? 0) + 1;
            }
        }

        arsort($types);
        
        return array_slice($types, 0, 3, true);
    }



    // CLIENT 
        //get or create a client
    static function getOrCreateClient(Request $request)
    {
        //validation logic
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:255',
        ]);
        try {
            $name = BaseService::getFirstSubstring($request->client_name);
            $client = Client::where('name', $request->client_name)
                ->where('phone', $request->client_phone)
                ->first();
            if (!$client) {
                // Create a new client if it doesn't exist
                $client = Client::create([
                    'name' => $name,
                    'phone' => $request->client_phone,
                    'address' => $request->client_address ?? null,
                    'description' => $request->client_name ?? null,
                ]);
            }
            else {
                // Update existing client with new details
                $client->update([
                    'description' => $request->client_name ?? $client->description,
                ]);
            }
        } catch (\Throwable $th) {
            return null;
        }
        // Logic to get or create a client
        return $client;
    }

    static function getFirstSubstring($text)
    {
        if (empty($text)) {
            return '';
        }
        $commaPosition = strpos($text, ',');

        if ($commaPosition === false) {
            return trim($text);
        }
        return trim(substr($text, 0, $commaPosition));
    }

    //MACHINE

    //get machine by company ID
    static function getMachineIdsByCompanyId($companyId)
    {
        $machineIds = Machine::where('company_id', $companyId)
            ->where('is_active', true)
            ->get()
            ->pluck('id');
            return  $machineIds->isEmpty() ? null : $machineIds;
    }


    //DASHBOARD

        /**
     * Get dashboard statistics for collections
     *
     * @param array $machineIds
     * @param string|null $date
     * @return array
     */
    public static function getDashboardStats($machineIds = null, $date = null): array
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        $yesterday = $targetDate->copy()->subDay();

        return [
            'total_collections_today' => self::getTotalCollectionsToday($machineIds, $targetDate),
            'total_collections_yesterday' => self::getTotalCollectionsToday($machineIds, $yesterday),
            'collector_stats' => self::getCollectorStats($machineIds, $targetDate),
            'machine_counts' => self::getMachineCounts($machineIds),
            'total_transactions_today' => self::getTotalTransactionsToday($machineIds, $targetDate),
        ];
    }

    /**
     * Get total collections for today
     *
     * @param array|null $machineIds
     * @param Carbon $date
     * @return array
     */
    public static function getTotalCollectionsToday($machineIds = null, Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        
        $query = Collection::whereDate('date', $date);
        
        if ($machineIds) {
            $query->whereIn('machine_id', $machineIds);
        }

        $collections = $query->with('collectionItems')->get();
        
        $totalAmount = $collections->sum(function ($collection) {
            return $collection->collectionItems->sum('amount');
        });

        return [
            'amount' => $totalAmount,
            'formatted_amount' => self::formatCurrency($totalAmount),
            'count' => $collections->count(),
        ];
    }

    /**
     * Get collector-specific statistics
     *
     * @param array|null $machineIds
     * @param Carbon $date
     * @return array
     */
    public static function getCollectorStats($machineIds = null, Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $yesterday = $date->copy()->subDay();

        $query = Collection::whereDate('date', $date);
        
        if ($machineIds) {
            $query->whereIn('machine_id', $machineIds);
        }

        $collections = $query->with(['collectionItems', 'collector'])->get();
        
        // Group by collector
        $collectorStats = $collections->groupBy('collector_id')->map(function ($collectorCollections) use ($yesterday, $machineIds) {
            $collector = $collectorCollections->first()->collector;
            
            $todayAmount = $collectorCollections->sum(function ($collection) {
                return $collection->collectionItems->sum('amount');
            });

            // Get yesterday's amount for this collector
            $yesterdayQuery = Collection::whereDate('date', $yesterday)
                ->where('collector_id', $collector->id);
            
            if ($machineIds) {
                $yesterdayQuery->whereIn('machine_id', $machineIds);
            }
            
            $yesterdayAmount = $yesterdayQuery->with('collectionItems')->get()
                ->sum(function ($collection) {
                    return $collection->collectionItems->sum('amount');
                });

            $percentageChange = $yesterdayAmount > 0 
                ? (($todayAmount - $yesterdayAmount) / $yesterdayAmount) * 100 
                : ($todayAmount > 0 ? 100 : 0);

            return [
                'collector_name' => $collector->name,
                'today_amount' => $todayAmount,
                'yesterday_amount' => $yesterdayAmount,
                'formatted_today' => self::formatCurrency($todayAmount),
                'formatted_yesterday' => self::formatCurrency($yesterdayAmount),
                'percentage_change' => round($percentageChange, 1),
                'collections_count' => $collectorCollections->count(),
            ];
        })->values();

        return $collectorStats->toArray();
    }

    /**
     * Get machine counts by collector/company
     *
     * @param array|null $machineIds
     * @return array
     */
    public static function getMachineCounts($machineIds = null): array
    {
        $query = Machine::where('is_active', true);
        
        if ($machineIds) {
            $query->whereIn('id', $machineIds);
        }

        $machines = $query->with('company')->get();
        
        // Group by company or collector (you can modify this logic)
        $machineCounts = $machines->groupBy('company.name')->map(function ($companyMachines, $companyName) {
            return [
                'company_name' => $companyName,
                'machine_count' => $companyMachines->count(),
                'machines' => $companyMachines->pluck('name')->toArray(),
            ];
        })->values();

        return $machineCounts->toArray();
    }

    /**
     * Get total transactions count for today
     *
     * @param array|null $machineIds
     * @param Carbon $date
     * @return int
     */
    public static function getTotalTransactionsToday($machineIds = null, Carbon $date = null): int
    {
        $date = $date ?? Carbon::today();
        
        $query = CollectionItem::whereHas('collection', function ($q) use ($date) {
            $q->whereDate('date', $date);
        });
        
        if ($machineIds) {
            $query->whereHas('collection', function ($q) use ($machineIds) {
                $q->whereIn('machine_id', $machineIds);
            });
        }

        return $query->count();
    }

    /**
     * Format currency amount
     *
     * @param float $amount
     * @return string
     */
    public static function formatCurrency($amount): string
    {
        if ($amount >= 1000000) {
            return number_format($amount / 1000000, 1) . 'M/=';
        } elseif ($amount >= 1000) {
            return number_format($amount / 1000, 1) . 'K/=';
        } else {
            return number_format($amount, 0) . '/=';
        }
    }

    /**
     * Calculate percentage change
     *
     * @param float $current
     * @param float $previous
     * @return float
     */
    public static function calculatePercentageChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Get top performing collectors
     *
     * @param array|null $machineIds
     * @param Carbon $date
     * @param int $limit
     * @return array
     */
    public static function getTopCollectors($machineIds = null, Carbon $date = null, $limit = 5): array
    {
        $date = $date ?? Carbon::today();
        
        $query = Collection::whereDate('date', $date);
        
        if ($machineIds) {
            $query->whereIn('machine_id', $machineIds);
        }

        $collections = $query->with(['collectionItems', 'collector'])->get();
        
        $collectorTotals = $collections->groupBy('collector_id')->map(function ($collectorCollections) {
            $collector = $collectorCollections->first()->collector;
            $totalAmount = $collectorCollections->sum(function ($collection) {
                return $collection->collectionItems->sum('amount');
            });

            return [
                'collector_name' => $collector->name,
                'total_amount' => $totalAmount,
                'formatted_amount' => self::formatCurrency($totalAmount),
                'collections_count' => $collectorCollections->count(),
            ];
        })->sortByDesc('total_amount')->take($limit)->values();

        return $collectorTotals->toArray();
    }

    /**
     * Get collections trend for the last N days
     *
     * @param array|null $machineIds
     * @param int $days
     * @return array
     */
    public static function getCollectionsTrend($machineIds = null, $days = 7): array
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays($days - 1);

        $query = Collection::whereBetween('date', [$startDate, $endDate]);
        
        if ($machineIds) {
            $query->whereIn('machine_id', $machineIds);
        }

        $collections = $query->with('collectionItems')->get();
        
        $trendData = [];
        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dayCollections = $collections->where('date', $currentDate->toDateString());
            
            $totalAmount = $dayCollections->sum(function ($collection) {
                return $collection->collectionItems->sum('amount');
            });

            $trendData[] = [
                'date' => $currentDate->toDateString(),
                'formatted_date' => $currentDate->format('M d'),
                'amount' => $totalAmount,
                'formatted_amount' => self::formatCurrency($totalAmount),
                'collections_count' => $dayCollections->count(),
            ];
        }

        return $trendData;
    }
}