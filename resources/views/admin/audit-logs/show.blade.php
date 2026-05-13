<x-admin-layout title="Audit Log">
    <div class="mb-4"><a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Audit Logs</a></div>
    <x-admin.page-header title="Audit Log Entry" />

    <div class="bg-white rounded-xl border border-gray-200 p-5 text-sm space-y-2 mb-6">
        <p><span class="text-gray-500 w-36 inline-block">When</span> {{ $auditLog->created_at->format('Y-m-d H:i:s') }}</p>
        <p><span class="text-gray-500 w-36 inline-block">User</span> {{ $auditLog->user?->name ?? 'System' }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Action</span> <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $auditLog->action }}</span></p>
        <p><span class="text-gray-500 w-36 inline-block">Target Type</span> {{ class_basename($auditLog->auditable_type) }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Target ID</span> {{ $auditLog->auditable_id }}</p>
        <p><span class="text-gray-500 w-36 inline-block">IP Address</span> {{ $auditLog->ip_address ?? '—' }}</p>
        <p><span class="text-gray-500 w-36 inline-block">User Agent</span> <span class="text-xs text-gray-500">{{ $auditLog->user_agent ?? '—' }}</span></p>
    </div>

    @if ($auditLog->changes)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Changes</h3>
            <pre class="text-xs bg-gray-50 rounded-lg p-4 overflow-x-auto whitespace-pre-wrap text-gray-700">{{ json_encode($auditLog->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif
</x-admin-layout>
