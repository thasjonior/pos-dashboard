<x-admin-layout title="Dashboard">

    <x-admin.page-header title="Dashboard" subtitle="Today's overview" />

    <!-- Top stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-admin.stat-card
            title="Revenue Today"
            :value="$main['amount']"
            :delta="$main['percent_difference']"
            tone="blue" />
        <x-admin.stat-card
            title="Today's Transactions"
            :value="number_format($todayTransactions)"
            tone="green" />
        <x-admin.stat-card
            title="Companies"
            :value="$companyData->count()"
            tone="neutral" />
    </div>

    <!-- Per-company cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach ($companyData as $company)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-medium text-gray-800">{{ $company['name'] }}</span>
                    <span class="text-xs font-mono bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ $company['slug'] }}</span>
                </div>
                <p class="text-2xl font-semibold text-gray-900">{{ $company['summary']['amount'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $company['machine_count'] }} machine(s)</p>
                <p class="text-xs mt-1 {{ $company['summary']['trend'] === 'up' ? 'text-green-600' : ($company['summary']['trend'] === 'down' ? 'text-red-500' : 'text-gray-400') }}">
                    {{ $company['summary']['percent_difference'] }} vs yesterday
                </p>
            </div>
        @endforeach
    </div>

    <!-- Recent collections -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Recent Collections</h2>
        </div>
        @if ($recentCollections->isEmpty())
            <x-admin.empty-state title="No collections yet" />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Receipt #</th>
                        <th class="px-5 py-3 text-left">Client</th>
                        <th class="px-5 py-3 text-left">Machine</th>
                        <th class="px-5 py-3 text-left">Company</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($recentCollections as $col)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs">
                                <a href="{{ route('admin.collections.show', $col) }}" class="text-blue-600 hover:underline">
                                    {{ $col->receipt_id }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $col->client_name ?? $col->client?->name ?? 'Walk-in' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $col->machine?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-mono">
                                    {{ $col->machine?->company?->slug ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium">{{ number_format($col->amount) }}/=</td>
                            <td class="px-5 py-3 text-right text-gray-400 text-xs">{{ $col->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</x-admin-layout>
