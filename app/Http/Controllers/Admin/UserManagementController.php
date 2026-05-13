<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::admins()->latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => ['required', Rule::in(['admin', 'super_admin'])],
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        AuditLog::record(auth()->user(), 'created', $user);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created. Password: {$data['password']} — copy this, it won't be shown again.");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['required', Rule::in(['admin', 'super_admin'])],
        ]);

        // Block self-demotion
        if ($user->id === auth()->id() && $data['role'] !== 'super_admin' && $user->isSuperAdmin()) {
            return back()->withErrors(['role' => 'You cannot demote yourself from super_admin.']);
        }

        $before = $user->only(['name', 'email', 'role']);
        $user->update($data);
        AuditLog::record(auth()->user(), 'updated', $user, ['before' => $before, 'after' => $data]);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function deactivate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['deactivate' => 'You cannot deactivate yourself.']);
        }

        $superAdminCount = User::where('role', 'super_admin')->where('is_active', true)->count();
        if ($user->isSuperAdmin() && $superAdminCount <= 1) {
            return back()->withErrors(['deactivate' => 'Cannot deactivate the last super_admin.']);
        }

        $user->update(['is_active' => false]);
        AuditLog::record(auth()->user(), 'deactivated', $user);

        return back()->with('success', "{$user->name} deactivated.");
    }

    public function destroy(User $user)
    {
        // Soft-deactivate instead of hard delete for admin users
        return $this->deactivate(new Request(), $user);
    }
}
