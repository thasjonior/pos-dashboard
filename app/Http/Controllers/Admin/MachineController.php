<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Collection;
use App\Models\Company;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $machines = Machine::with(['company', 'collector'])
            ->when($request->company_id, fn ($q) => $q->where('company_id', $request->company_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', "%{$request->search}%")
                   ->orWhere('serial_number', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $companies = Company::orderBy('name')->get();

        return view('admin.machines.index', compact('machines', 'companies'));
    }

    public function show(Request $request, Machine $machine)
    {
        $from = $request->from ?? now()->subDays(6)->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $collections = Collection::with(['client', 'collectionItems.collectionType'])
            ->where('machine_id', $machine->id)
            ->whereBetween('date', [$from, $to])
            ->latest('date')
            ->paginate(20)
            ->withQueryString();

        $totalRevenue     = Collection::where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->sum('amount');
        $transactionCount = Collection::where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->count();
        $lastSync         = Collection::where('machine_id', $machine->id)->latest('created_at')->value('created_at');

        $machine->load(['company', 'collector']);

        return view('admin.machines.show', compact(
            'machine', 'collections', 'from', 'to',
            'totalRevenue', 'transactionCount', 'lastSync',
        ));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();

        return view('admin.machines.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'         => 'required|exists:companies,id',
            'serial_number'      => 'required|string|max:100',
            'name'               => ['required', 'string', 'max:100', 'unique:machines,name'],
            'type'               => ['required', Rule::in(['terminal', 'mobile'])],
            'status'             => ['required', Rule::in(['active', 'inactive', 'maintenance'])],
            'installation_date'  => 'required|date',
            'description'        => 'nullable|string|max:1000',
            'account_phone'      => 'nullable|string|max:30',
            'account_password'   => 'required|string|min:6',
        ]);

        DB::transaction(function () use ($data, &$machine, &$account) {
            $account = User::create([
                'name'         => $data['name'],
                'phone'        => $data['account_phone'] ?? null,
                'machine_name' => $data['name'],
                'password'     => Hash::make($data['account_password']),
                'role'         => 'collector',
            ]);

            $machine = Machine::create([
                'company_id'        => $data['company_id'],
                'serial_number'     => $data['serial_number'],
                'name'              => $data['name'],
                'type'              => $data['type'],
                'status'            => $data['status'],
                'installation_date' => $data['installation_date'],
                'description'       => $data['description'] ?? null,
                'collector_id'      => $account->id,
                'is_active'         => $data['status'] === 'active',
            ]);

            AuditLog::record(auth()->user(), 'created', $machine, [
                'device_account_user_id' => $account->id,
            ]);
        });

        return redirect()->route('admin.machines.show', $machine)
            ->with('success', "Machine {$machine->name} created. Device account login: machine name = {$machine->name}, password = {$data['account_password']}. Save this — it won't be shown again.");
    }

    public function edit(Machine $machine)
    {
        $machine->load(['company', 'collector']);
        $companies = Company::orderBy('name')->get();

        return view('admin.machines.edit', compact('machine', 'companies'));
    }

    public function update(Request $request, Machine $machine)
    {
        $data = $request->validate([
            'type'        => ['required', Rule::in(['terminal', 'mobile'])],
            'status'      => ['required', Rule::in(['active', 'inactive', 'maintenance'])],
            'description' => 'nullable|string|max:1000',
        ]);

        $before = $machine->only(['status', 'type', 'description']);
        $machine->update($data);
        AuditLog::record(auth()->user(), 'updated', $machine, ['before' => $before, 'after' => $data]);

        return redirect()->route('admin.machines.show', $machine)->with('success', 'Machine updated.');
    }

    public function destroy(Machine $machine)
    {
        if ($machine->collections()->exists()) {
            $count = $machine->collections()->count();
            return back()->withErrors(['delete' => "Cannot delete: machine has {$count} collection(s). Set status to inactive instead."]);
        }

        AuditLog::record(auth()->user(), 'deleted', $machine);
        $machine->delete();

        return redirect()->route('admin.machines.index')->with('success', 'Machine deleted.');
    }
}
