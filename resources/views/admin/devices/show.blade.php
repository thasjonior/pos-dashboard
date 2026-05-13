<x-admin-layout :title="'Device ' . $device->device_id">
    <div class="mb-4"><a href="{{ route('admin.devices.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Devices</a></div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @error('confirm_device_id')
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $message }}</div>
    @enderror

    <div class="bg-white rounded-xl border border-gray-200 p-5 text-sm space-y-2 mb-6">
        <p><span class="text-gray-500 w-36 inline-block">Device ID</span> <span class="font-mono">{{ $device->device_id }}</span></p>
        <p><span class="text-gray-500 w-36 inline-block">Machine</span> {{ $device->machine?->name ?? '—' }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Company</span> {{ $device->machine?->company?->name ?? '—' }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Registered</span> {{ $device->created_at->format('Y-m-d H:i') }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Wipe Command</span>
            @if ($device->wipe_command)
                <span class="text-amber-600 font-medium">Active</span>
            @else
                <span class="text-gray-400">None</span>
            @endif
        </p>
        <p><span class="text-gray-500 w-36 inline-block">Wipe Requested</span> {{ $device->wipe_requested_at?->format('Y-m-d H:i') ?? '—' }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Wipe Completed</span> {{ $device->wipe_completed_at?->format('Y-m-d H:i') ?? '—' }}</p>
    </div>

    @if (auth()->user()->isSuperAdmin() && $device->wipe_requested_at && !$device->wipe_completed_at && !$device->wipe_command && $device->wipe_requested_at->lt(now()->subHours(24)))
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 max-w-md mb-4">
            <h3 class="text-sm font-semibold text-amber-700 mb-2">Wipe stuck in pending</h3>
            <p class="text-xs text-amber-600 mb-3">The wipe was requested over 24 hours ago but the device hasn't confirmed. If the device was physically wiped or is permanently offline, you can manually mark it as executed.</p>
            <form method="POST" action="{{ route('admin.devices.mark-executed', $device) }}">
                @csrf
                <button type="submit"
                        onclick="return confirm('Mark this wipe as executed? Do this only if you are certain the device has been wiped.')"
                        class="text-sm bg-amber-600 hover:bg-amber-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    Mark executed manually
                </button>
            </form>
        </div>
    @endif

    @if (auth()->user()->isSuperAdmin() && !$device->wipe_completed_at)
        <div class="bg-red-50 border border-red-200 rounded-xl p-5 max-w-md" x-data="{ confirm: '' }">
            <h3 class="text-sm font-semibold text-red-700 mb-3">Trigger Remote Wipe</h3>
            <p class="text-xs text-red-600 mb-4">This will queue a wipe command. The device will factory-reset on its next status poll. <strong>This cannot be undone.</strong></p>
            <form method="POST" action="{{ route('admin.devices.wipe', $device) }}"
                  x-on:submit.prevent="if (confirm !== '{{ $device->device_id }}') { alert('Device ID does not match.'); return; } $el.submit()">
                @csrf
                <label class="block text-xs font-medium text-red-700 mb-1">Type the device ID to confirm:</label>
                <input type="text" name="confirm_device_id" x-model="confirm"
                       placeholder="{{ $device->device_id }}"
                       class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-red-400 focus:outline-none mb-3">
                <button type="submit"
                        :class="confirm === '{{ $device->device_id }}' ? 'bg-red-600 hover:bg-red-700' : 'bg-red-200 cursor-not-allowed'"
                        class="text-white text-sm font-medium px-4 py-2 rounded-lg transition w-full">
                    Wipe Device
                </button>
            </form>
        </div>
    @endif
</x-admin-layout>
