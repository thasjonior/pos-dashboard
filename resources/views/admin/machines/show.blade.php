<x-admin-layout :title="$machine->name">
    <div class="mb-4">
        <a href="{{ route('admin.machines.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Machines</a>
    </div>

    <!-- Machine header card -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6 flex flex-wrap gap-6 items-start">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ $machine->name }}</h1>
            <p class="text-sm text-gray-500">Serial: {{ $machine->serial_number ?? '—' }}</p>
        </div>
        <div class="text-sm space-y-1">
            <p><span class="text-gray-500">Company:</span> <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $machine->company?->slug }}</span></p>
            <p><span class="text-gray-500">Device account:</span> <span class="font-mono text-xs">{{ $machine->collector?->machine_name ?? '—' }}</span></p>
            <p><span class="text-gray-500">Type:</span> {{ ucfirst($machine->type) }}</p>
            <p><span class="text-gray-500">Status:</span> {{ ucfirst($machine->status) }}</p>
        </div>
        <div class="ml-auto">
            <a href="{{ route('admin.machines.edit', $machine) }}"
               class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition">Edit</a>
        </div>
    </div>

    <!-- Date filter + stats -->
    <form method="GET" class="flex gap-3 items-center mb-5">
        <input type="date" name="from" value="{{ $from }}"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <span class="text-gray-400 text-sm">to</span>
        <input type="date" name="to" value="{{ $to }}"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit" class="text-sm bg-blue-600 text-white px-4 py-1.5 rounded-lg hover:bg-blue-700 transition">Apply</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-admin.stat-card title="Revenue in Range" :value="number_format($totalRevenue) . '/='" tone="blue" />
        <x-admin.stat-card title="Transactions" :value="number_format($transactionCount)" tone="green" />
        <x-admin.stat-card title="Last Sync" :value="$lastSync ? $lastSync->diffForHumans() : 'Never'" tone="neutral" />
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 text-sm font-semibold text-gray-700">Collections</div>
        @if ($collections->isEmpty())
            <x-admin.empty-state title="No collections in this range" />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Receipt #</th>
                        <th class="px-5 py-3 text-left">Client</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($collections as $col)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs">
                                <a href="{{ route('admin.collections.show', $col) }}" class="text-blue-600 hover:underline">{{ $col->receipt_id }}</a>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $col->client_name ?? $col->client?->name ?? 'Walk-in' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $col->date }}</td>
                            <td class="px-5 py-3 text-right font-medium">{{ number_format($col->amount) }}/=</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-100">{{ $collections->links() }}</div>
        @endif
    </div>
</x-admin-layout>
