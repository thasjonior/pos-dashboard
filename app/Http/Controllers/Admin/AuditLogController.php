<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to,   fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->when($request->target_type, fn ($q) => $q->where('auditable_type', 'like', "%{$request->target_type}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $adminUsers = User::admins()->orderBy('name')->get();
        $actions    = AuditLog::distinct()->pluck('action')->sort()->values();

        return view('admin.audit-logs.index', compact('logs', 'adminUsers', 'actions'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
