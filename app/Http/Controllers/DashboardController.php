<?php
namespace App\Http\Controllers;

use App\Services\BaseService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard data for collections overview
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get machine IDs from request or user's company
            $machineIds = $this->getMachineIds($request);
            
            // Get date from request or use today
            $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
            $yesterday = $date->copy()->subDay();

            // Get all dashboard statistics
            $stats = BaseService::getDashboardStats($machineIds, $date);
            
            // Calculate percentage changes
            $totalToday = $stats['total_collections_today'];
            $totalYesterday = $stats['total_collections_yesterday'];
            
            $totalPercentageChange = BaseService::calculatePercentageChange(
                $totalToday['amount'], 
                $totalYesterday['amount']
            );

            // Format response for mobile app
            $response = [
                'success' => true,
                'data' => [
                    'total_collections_today' => [
                        'amount' => $totalToday['amount'],
                        'formatted_amount' => $totalToday['formatted_amount'],
                        'count' => $totalToday['count'],
                        'percentage_change' => round($totalPercentageChange, 1),
                        'yesterday_amount' => $totalYesterday['formatted_amount'],
                    ],
                    'collector_collections' => $stats['collector_stats'],
                    'machine_counts' => $stats['machine_counts'],
                    'total_transactions_today' => $stats['total_transactions_today'],
                    'top_collectors' => BaseService::getTopCollectors($machineIds, $date, 3),
                    'collections_trend' => BaseService::getCollectionsTrend($machineIds, 7),
                ],
                'meta' => [
                    'date' => $date->toDateString(),
                    'formatted_date' => $date->format('F j, Y'),
                    'machine_count' => count($machineIds ?? []),
                    'generated_at' => now()->toISOString(),
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific collector dashboard
     *
     * @param Request $request
     * @param string $collectorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function collectorDashboard(Request $request, $collectorId)
    {
        try {
            $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
            $machineIds = $this->getMachineIds($request);

            // Get collector-specific stats
            $collectorStats = BaseService::getCollectorStats($machineIds, $date);
            $specificCollector = collect($collectorStats)->firstWhere('collector_name', 'like', "%{$collectorId}%");

            if (!$specificCollector) {
                return response()->json([
                    'success' => false,
                    'message' => 'Collector not found'
                ], 404);
            }

            // Get collector's trend
            $collectorTrend = BaseService::getCollectionsTrend($machineIds, 7);

            return response()->json([
                'success' => true,
                'data' => [
                    'collector' => $specificCollector,
                    'trend' => $collectorTrend,
                    'machines_assigned' => BaseService::getMachineCounts($machineIds),
                ],
                'meta' => [
                    'date' => $date->toDateString(),
                    'collector_id' => $collectorId,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get collector dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get collections summary for a specific period
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function collectionsSummary(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'collector_id' => 'nullable|string',
            ]);

            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::today()->subDays(30);
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::today();
            $machineIds = $this->getMachineIds($request);

            // Get summary data
            $summary = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $dayStats = BaseService::getDashboardStats($machineIds, $currentDate);
                $summary[] = [
                    'date' => $currentDate->toDateString(),
                    'formatted_date' => $currentDate->format('M j'),
                    'total_amount' => $dayStats['total_collections_today']['amount'],
                    'formatted_amount' => $dayStats['total_collections_today']['formatted_amount'],
                    'transactions_count' => $dayStats['total_transactions_today'],
                    'collections_count' => $dayStats['total_collections_today']['count'],
                ];
                
                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'period' => [
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                        'days_count' => $startDate->diffInDays($endDate) + 1,
                    ],
                    'totals' => [
                        'total_amount' => collect($summary)->sum('total_amount'),
                        'total_transactions' => collect($summary)->sum('transactions_count'),
                        'total_collections' => collect($summary)->sum('collections_count'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get collections summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get machine IDs for the current user/request
     *
     * @param Request $request
     * @return array|null
     */
    private function getMachineIds(Request $request): ?array
    {
        // If machine IDs are provided in request
        if ($request->has('machine_ids')) {
            return $request->input('machine_ids');
        }

        // Get from authenticated user's company
        $user = $request->user();
        if ($user && $user->company_id) {
            return BaseService::getMachineIdsByCompanyId($user->company_id);
        }

        // Get from machine authentication (if using machine login)
        if ($request->has('machine_id')) {
            return [$request->input('machine_id')];
        }

        return null; // Return all machines if no filter
    }
}