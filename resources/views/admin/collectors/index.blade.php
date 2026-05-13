<x-admin-layout title="Collectors">
    <x-admin.page-header title="Collectors" subtitle="Create collectors via Machine registration." />

    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <select name="company_id" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Companies</option>
            @foreach ($companies as $c)
                <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or phone…"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit" class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-1.5 rounded-lg transition">Filter</button>
        @if (request()->hasAny(['company_id','search']))
            <a href="{{ route('admin.collectors.index') }}" class="text-sm text-gray-400 hover:text-gray-600 px-2 py-1.5">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if ($collectors->isEmpty())
            <x-admin.empty-state title="No collectors found" />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Phone</th>
                        <th class="px-5 py-3 text-left">Machine</th>
                        <th class="px-5 py-3 text-left">Company</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($collectors as $collector)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $collector->name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $collector->phone ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $collector->machine?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $collector->machine?->company?->slug ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if (!($collector->is_active ?? true))
                                    <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Inactive</span>
                                @else
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right space-x-3">
                                <a href="{{ route('admin.collectors.edit', $collector) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.collectors.deactivate', $collector) }}" class="inline"
                                      onsubmit="return confirm('Deactivate {{ addslashes($collector->name) }}?')">
                                    @csrf
                                    <button type="submit" class="text-amber-500 hover:underline text-xs">Deactivate</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-100">{{ $collectors->links() }}</div>
        @endif
    </div>
</x-admin-layout>
