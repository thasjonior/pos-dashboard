<x-admin-layout title="Machines">
    <x-admin.page-header title="Machines">
        <x-slot name="actions">
            <a href="{{ route('admin.machines.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                + Register Machine
            </a>
        </x-slot>
    </x-admin.page-header>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <select name="company_id" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Companies</option>
            @foreach ($companies as $c)
                <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Statuses</option>
            @foreach (['active','inactive','maintenance'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="type" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Types</option>
            @foreach (['mobile','terminal'] as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or serial…"
               class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit" class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-1.5 rounded-lg transition">Filter</button>
        @if (request()->hasAny(['company_id','status','type','search']))
            <a href="{{ route('admin.machines.index') }}" class="text-sm text-gray-400 hover:text-gray-600 px-2 py-1.5">Clear</a>
        @endif
    </form>

    @if ($errors->has('delete'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $errors->first('delete') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if ($machines->isEmpty())
            <x-admin.empty-state title="No machines found" description="Try adjusting your filters or register a new machine." />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Company</th>
                        <th class="px-5 py-3 text-left">Device Account</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($machines as $machine)
                        @php
                            $statusColors = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-500', 'maintenance' => 'bg-amber-100 text-amber-700'];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-medium">
                                <a href="{{ route('admin.machines.show', $machine) }}" class="text-blue-600 hover:underline">
                                    {{ $machine->name }}
                                </a>
                                @if ($machine->serial_number)
                                    <span class="text-xs text-gray-400 ml-1">#{{ $machine->serial_number }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $machine->company?->slug ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 font-mono text-xs">{{ $machine->collector?->machine_name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statusColors[$machine->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ ucfirst($machine->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ ucfirst($machine->type) }}</td>
                            <td class="px-5 py-3 text-right space-x-3">
                                <a href="{{ route('admin.machines.edit', $machine) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.machines.destroy', $machine) }}" class="inline"
                                      onsubmit="return confirm('Delete {{ addslashes($machine->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-100">{{ $machines->links() }}</div>
        @endif
    </div>
</x-admin-layout>
