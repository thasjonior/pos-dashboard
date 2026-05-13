<x-admin-layout :title="'Collection ' . $collection->receipt_id">
    <div class="mb-4"><a href="{{ route('admin.collections.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Collections</a></div>

    @php $isSynced = $collection->notes && str_contains($collection->notes, 'Synced from collector app'); @endphp

    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6 print:shadow-none">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Receipt</p>
                <p class="text-xl font-mono font-semibold text-gray-900">{{ $collection->receipt_id }}</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full {{ $isSynced ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $isSynced ? 'Synced' : 'Unsynced' }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-5">
            <div class="space-y-1">
                <p><span class="text-gray-500 w-24 inline-block">Client</span> {{ $collection->client_name ?? $collection->client?->name ?? 'Walk-in' }}</p>
                <p><span class="text-gray-500 w-24 inline-block">Machine</span> {{ $collection->machine?->name ?? '—' }}</p>
                <p><span class="text-gray-500 w-24 inline-block">Company</span> {{ $collection->machine?->company?->name ?? '—' }}</p>
            </div>
            <div class="space-y-1">
                <p><span class="text-gray-500 w-24 inline-block">Date</span> {{ $collection->date }}</p>
                <p><span class="text-gray-500 w-24 inline-block">Created</span> {{ $collection->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <!-- Items -->
        <table class="w-full text-sm mb-4">
            <thead class="text-xs text-gray-500 border-t border-b border-gray-100">
                <tr>
                    <th class="py-2 text-left">Item</th>
                    <th class="py-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($collection->collectionItems as $item)
                    <tr class="border-b border-gray-50">
                        <td class="py-2">{{ $item->collectionType?->name ?? '—' }}</td>
                        <td class="py-2 text-right font-mono">{{ number_format($item->amount) }}/=</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="py-4 text-center text-gray-400">No items</td></tr>
                @endforelse
            </tbody>
            <tfoot class="border-t border-gray-200">
                <tr>
                    <td class="py-2 font-semibold">Total</td>
                    <td class="py-2 text-right font-semibold font-mono">{{ number_format($collection->amount) }}/=</td>
                </tr>
            </tfoot>
        </table>

        @if ($collection->notes)
            <details class="text-xs text-gray-400">
                <summary class="cursor-pointer hover:text-gray-600">Notes</summary>
                <pre class="mt-2 whitespace-pre-wrap">{{ $collection->notes }}</pre>
            </details>
        @endif
    </div>

    <button onclick="window.print()" class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition print:hidden">Print</button>

    <style>@media print { .md\:ml-60 { margin-left: 0 !important; } header, nav, footer, button { display: none !important; } }</style>
</x-admin-layout>
