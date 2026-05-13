<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Company;
use App\Models\Machine;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $from   = $request->from ?? now()->toDateString();
        $to     = $request->to   ?? now()->toDateString();
        $synced = $request->synced; // 'synced' | 'unsynced' | null

        $query = $this->buildQuery($request, $from, $to, $synced);

        $collections = $query->with(['client', 'machine.company'])
            ->latest('collections.created_at')
            ->paginate(30)
            ->withQueryString();

        $companies = Company::orderBy('name')->get();
        $machines  = Machine::orderBy('name')->get();

        return view('admin.collections.index', compact('collections', 'companies', 'machines', 'from', 'to', 'synced'));
    }

    public function show(Collection $collection)
    {
        $collection->load(['client', 'machine.company', 'collectionItems.collectionType']);

        return view('admin.collections.show', compact('collection'));
    }

    public function export(Request $request)
    {
        $from   = $request->from ?? now()->toDateString();
        $to     = $request->to   ?? now()->toDateString();
        $synced = $request->synced;

        $collections = $this->buildQuery($request, $from, $to, $synced)
            ->with(['client', 'machine.company', 'collectionItems.collectionType'])
            ->latest('collections.created_at')
            ->get();

        $filename = "collections_{$from}_{$to}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($collections) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Receipt #', 'Date', 'Client', 'Machine', 'Company',
                'Item', 'Item Amount', 'Total', 'Sync Status', 'Notes',
            ]);

            foreach ($collections as $col) {
                $items = $col->collectionItems;
                if ($items->isEmpty()) {
                    fputcsv($handle, [
                        $col->receipt_id, $col->date,
                        $col->client_name ?? $col->client?->name,
                        $col->machine?->name, $col->machine?->company?->name,
                        '', '', number_format($col->amount, 2),
                        $this->syncLabel($col->notes), $col->notes,
                    ]);
                } else {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $col->receipt_id, $col->date,
                            $col->client_name ?? $col->client?->name,
                            $col->machine?->name, $col->machine?->company?->name,
                            $item->collectionType?->name, number_format($item->amount, 2),
                            number_format($col->amount, 2),
                            $this->syncLabel($col->notes), $col->notes,
                        ]);
                    }
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildQuery(Request $request, string $from, string $to, ?string $synced)
    {
        return Collection::whereBetween('date', [$from, $to])
            ->when($request->company_id, fn ($q) =>
                $q->whereHas('machine', fn ($m) => $m->where('company_id', $request->company_id)))
            ->when($request->machine_id, fn ($q) => $q->where('machine_id', $request->machine_id))
            ->when($synced === 'synced', fn ($q) =>
                $q->where('notes', 'like', '%Synced from collector app%'))
            ->when($synced === 'unsynced', fn ($q) =>
                $q->where(function ($q2) {
                    $q2->whereNull('notes')
                       ->orWhere('notes', 'not like', '%Synced from collector app%');
                }))
            ->when($request->client, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('client_name', 'like', "%{$request->client}%")
                   ->orWhereHas('client', fn ($c) =>
                       $c->where('name', 'like', "%{$request->client}%")
                         ->orWhere('phone', 'like', "%{$request->client}%"));
            }))
            ->when($request->receipt, fn ($q) => $q->where('receipt_id', 'like', "%{$request->receipt}%"));
    }

    private function syncLabel(?string $notes): string
    {
        if ($notes && str_contains($notes, 'Synced from collector app')) {
            return 'Synced';
        }

        return 'Unsynced';
    }
}
