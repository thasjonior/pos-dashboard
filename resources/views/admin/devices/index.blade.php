<x-admin-layout title="Devices">
    <x-admin.page-header title="Devices" subtitle="Remote wipe management for registered POS devices." />

    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">All Devices</option>
            <option value="pending"  @selected(request('status')==='pending')>Wipe Pending</option>
            <option value="executed" @selected(request('status')==='executed')>Wipe Executed</option>
            <option value="none"     @selected(request('status')==='none')>No Wipe</option>
        </select>
        <button type="submit" class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-1.5 rounded-lg transition">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if ($devices->isEmpty())
            <x-admin.empty-state title="No devices registered" />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Device ID</th>
                        <th class="px-5 py-3 text-left">Machine</th>
                        <th class="px-5 py-3 text-left">Company</th>
                        <th class="px-5 py-3 text-left">Wipe Status</th>
                        <th class="px-5 py-3 text-left">Registered</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($devices as $device)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs">
                                <a href="{{ route('admin.devices.show', $device) }}" class="text-blue-600 hover:underline">{{ $device->device_id }}</a>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $device->machine?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $device->machine?->company?->slug ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($device->wipe_completed_at)
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Wiped {{ $device->wipe_completed_at->diffForHumans() }}</span>
                                @elseif ($device->wipe_command)
                                    <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Wipe Pending</span>
                                @else
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Normal</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $device->created_at->format('Y-m-d') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.devices.show', $device) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-100">{{ $devices->links() }}</div>
        @endif
    </div>
</x-admin-layout>
