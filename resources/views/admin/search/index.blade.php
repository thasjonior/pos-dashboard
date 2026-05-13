<x-admin-layout title="Search">
    <x-admin.page-header :title="'Search results for &quot;' . $q . '&quot;'" />

    @if ($machines->isEmpty() && $collectors->isEmpty() && $clients->isEmpty() && $collections->isEmpty())
        <x-admin.empty-state title="No results found" :subtitle="'No matches for &quot;' . $q . '&quot;'" />
    @endif

    @if ($machines->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Machines</h2>
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-50">
                @foreach ($machines as $machine)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div>
                            <a href="{{ route('admin.machines.show', $machine) }}" class="text-sm font-medium text-blue-600 hover:underline">{{ $machine->name }}</a>
                            <span class="text-xs text-gray-400 ml-2">{{ $machine->company?->name }}</span>
                        </div>
                        <span class="text-xs {{ $machine->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} px-2 py-0.5 rounded-full">{{ $machine->status ?? ($machine->is_active ? 'active' : 'inactive') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($collectors->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Collectors</h2>
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-50">
                @foreach ($collectors as $collector)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div>
                            <a href="{{ route('admin.collectors.show', $collector) }}" class="text-sm font-medium text-blue-600 hover:underline">{{ $collector->name }}</a>
                            <span class="text-xs text-gray-400 ml-2">{{ $collector->phone }}</span>
                        </div>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ $collector->machine?->name ?? 'Unassigned' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($clients->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Clients</h2>
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-50">
                @foreach ($clients as $client)
                    <div class="px-5 py-3 hover:bg-gray-50">
                        <span class="text-sm font-medium">{{ $client->name }}</span>
                        <span class="text-xs text-gray-400 ml-2">{{ $client->phone ?? '—' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($collections->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Collections</h2>
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-50">
                @foreach ($collections as $col)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div>
                            <a href="{{ route('admin.collections.show', $col) }}" class="text-sm font-mono text-blue-600 hover:underline">{{ $col->receipt_id }}</a>
                            <span class="text-xs text-gray-400 ml-2">{{ $col->machine?->company?->slug }}</span>
                        </div>
                        <span class="text-sm font-medium">{{ number_format($col->amount) }}/=</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-admin-layout>
