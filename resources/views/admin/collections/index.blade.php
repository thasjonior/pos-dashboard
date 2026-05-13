<x-admin-layout title="Collections">
    <x-admin.page-header title="Collections">
        <x-slot name="actions">
            <a href="{{ route('admin.collections.export', request()->query()) }}"
               class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition">Export CSV</a>
        </x-slot>
    </x-admin.page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="date" name="from" value="{{ $from }}"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <input type="date" name="to" value="{{ $to }}"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <select name="company_id" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Companies</option>
            @foreach ($companies as $c)
                <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="machine_id" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Machines</option>
            @foreach ($machines as $m)
                <option value="{{ $m->id }}" @selected(request('machine_id') == $m->id)>{{ $m->name }}</option>
            @endforeach
        </select>
        <select name="synced" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Sync Status</option>
            <option value="synced"   @selected(request('synced')==='synced')>Synced</option>
            <option value="unsynced" @selected(request('synced')==='unsynced')>Unsynced</option>
        </select>
        <input type="text" name="client" value="{{ request('client') }}" placeholder="Client name/phone…"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <input type="text" name="receipt" value="{{ request('receipt') }}" placeholder="Receipt #…"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit" class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-1.5 rounded-lg transition">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if ($collections->isEmpty())
            <x-admin.empty-state title="No collections for this filter" />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Receipt #</th>
                        <th class="px-5 py-3 text-left">Client</th>
                        <th class="px-5 py-3 text-left">Machine</th>
                        <th class="px-5 py-3 text-left">Company</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-left">Sync</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($collections as $col)
                        @php $isSynced = $col->notes && str_contains($col->notes, 'Synced from collector app'); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs">
                                <a href="{{ route('admin.collections.show', $col) }}" class="text-blue-600 hover:underline">{{ $col->receipt_id }}</a>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $col->client_name ?? $col->client?->name ?? 'Walk-in' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $col->machine?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $col->machine?->company?->slug ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $col->date }}</td>
                            <td class="px-5 py-3 text-right font-medium">{{ number_format($col->amount) }}/=</td>
                            <td class="px-5 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $isSynced ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $isSynced ? 'Synced' : 'Unsynced' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-100">{{ $collections->links() }}</div>
        @endif
    </div>
</x-admin-layout>
