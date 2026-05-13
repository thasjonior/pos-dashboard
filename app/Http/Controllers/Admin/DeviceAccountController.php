<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeviceAccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = User::collectors()
            ->with(['machine.company'])
            ->when($request->company_id, fn ($q) => $q->whereHas('machine', fn ($m) => $m->where('company_id', $request->company_id)))
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('machine_name', 'like', "%{$request->search}%")
                   ->orWhere('name', 'like', "%{$request->search}%")
                   ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $companies = Company::orderBy('name')->get();

        return view('admin.device-accounts.index', compact('accounts', 'companies'));
    }

    public function show(User $collector)
    {
        $collector->load('machine.company');

        return view('admin.device-accounts.show', compact('collector'));
    }

    public function edit(User $collector)
    {
        return view('admin.device-accounts.edit', compact('collector'));
    }

    public function update(Request $request, User $collector)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
        ]);

        $before = $collector->only(['name', 'phone']);

        $updateData = [
            'name'  => $data['name'],
            'phone' => $data['phone'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $collector->update($updateData);

        AuditLog::record(auth()->user(), 'updated', $collector, [
            'before' => $before,
            'after'  => $collector->only(['name', 'phone']),
            'note'   => 'device account updated',
        ]);

        return redirect()->route('admin.device-accounts.index')->with('success', 'Device account updated.');
    }

    public function deactivate(User $collector)
    {
        $collector->update(['is_active' => false]);
        AuditLog::record(auth()->user(), 'deactivated', $collector, ['note' => 'device account deactivated']);

        return back()->with('success', "{$collector->machine_name} deactivated.");
    }
}
