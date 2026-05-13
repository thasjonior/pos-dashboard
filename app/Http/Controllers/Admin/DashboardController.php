<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Company;
use App\Services\BaseService;
use App\Services\TimeRange;

class DashboardController extends Controller
{
    public function index()
    {
        $timeRange = TimeRange::TODAY;
        $companies = Company::with('machines')->get();

        $main = BaseService::getSummaryForMachines($timeRange, null);
        $todayTransactions = BaseService::getTransactionsCount($timeRange);

        $companyData = $companies->map(fn (Company $c) => [
            'id'            => $c->id,
            'slug'          => $c->slug,
            'name'          => $c->name,
            'summary'       => BaseService::getSummaryForMachines($timeRange, $c->machines->pluck('id')->all()),
            'machine_count' => $c->machines->count(),
        ])->values();

        $recentCollections = Collection::with(['machine.company', 'client'])
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('admin.dashboard.index', compact(
            'main',
            'todayTransactions',
            'companyData',
            'recentCollections',
        ));
    }
}
