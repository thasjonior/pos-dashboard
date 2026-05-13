<x-admin-layout title="Audit Logs">
    <x-admin.page-header title="Audit Logs" subtitle="All admin actions are recorded here." />

    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="date" name="from" value="{{ request('from') }}"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <input type="date" name="to" value="{{ request('to') }}"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <select name="user_id" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Users</option>
            @foreach ($adminUsers as $u)
                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
        <select name="action" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Actions</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
            @endforeach
        </select>
        <input type="text" name="target_type" value="{{ request('target_type') }}" placeholder="Model type…"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit" class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-1.5 rounded-lg transition">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if ($logs->isEmpty())
            <x-admin.empty-state title="No audit logs for this filter" />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">When</th>
                        <th class="px-5 py-3 text-left">User</th>
                        <th class="px-5 py-3 text-left">Action</th>
                        <th class="px-5 py-3 text-left">Target</th>
                        <th class="px-5 py-3 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $log->action }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 text-xs">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>
</x-admin-layout>
