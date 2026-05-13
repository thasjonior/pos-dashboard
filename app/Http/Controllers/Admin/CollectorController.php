<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CollectorController extends Controller
{
    public function index(Request $request)
    {
        $collectors = User::collectors()
            ->with(['machine.company'])
            ->when($request->company_id, fn ($q) => $q->whereHas('machine', fn ($m) => $m->where('company_id', $request->company_id)))
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', "%{$request->search}%")
                   ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $companies = Company::orderBy('name')->get();

        return view('admin.collectors.index', compact('collectors', 'companies'));
    }

    public function show(User $collector)
    {
        $collector->load('machine.company');

        return view('admin.collectors.show', compact('collector'));
    }

    public function edit(User $collector)
    {
        $collector->load('machine');

        // Machines available for reassignment: unassigned OR currently assigned to this collector
        $availableMachines = Machine::with('company')
            ->where(function ($q) use ($collector) {
                $q->whereNull('collector_id')
                  ->orWhere('collector_id', $collector->id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.collectors.edit', compact('collector', 'availableMachines'));
    }

    public function update(Request $request, User $collector)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:30',
            'machine_id' => 'nullable|exists:machines,id',
            'password'   => 'nullable|string|min:6',
        ]);

        $before = $collector->only(['name', 'phone', 'machine_name']);

        // Reassign machine if changed
        $newMachine = $data['machine_id'] ? Machine::find($data['machine_id']) : null;
        if ($newMachine && $newMachine->collector_id !== $collector->id) {
            // Detach old machine
            if ($collector->machine) {
                $collector->machine->update(['collector_id' => null, 'machine_name' => null]);
            }
            $newMachine->update(['collector_id' => $collector->id]);
            $collector->machine_name = $newMachine->name;
        }

        $updateData = [
            'name'  => $data['name'],
            'phone' => $data['phone'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if ($collector->machine_name) {
            $updateData['machine_name'] = $collector->machine_name;
        }

        $collector->update($updateData);

        AuditLog::record(auth()->user(), 'updated', $collector, [
            'before' => $before,
            'after'  => $collector->only(['name', 'phone', 'machine_name']),
        ]);

        return redirect()->route('admin.collectors.index')->with('success', 'Collector updated.');
    }

    public function deactivate(User $collector)
    {
        $collector->update(['is_active' => false]);
        AuditLog::record(auth()->user(), 'deactivated', $collector);

        return back()->with('success', "{$collector->name} deactivated.");
    }
}
