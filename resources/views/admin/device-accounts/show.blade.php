<x-admin-layout :title="$collector->machine_name ?? $collector->name">
    <div class="mb-4"><a href="{{ route('admin.device-accounts.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Device accounts</a></div>
    <x-admin.page-header :title="$collector->machine_name ?? $collector->name">
        <x-slot name="actions">
            <a href="{{ route('admin.device-accounts.edit', $collector) }}" class="bg-gray-100 hover:bg-gray-200 text-sm px-4 py-2 rounded-lg transition">Edit</a>
        </x-slot>
    </x-admin.page-header>

    <div class="bg-white rounded-xl border border-gray-200 p-5 text-sm space-y-2">
        <p><span class="text-gray-500 w-36 inline-block">Machine name (login)</span> <span class="font-mono text-xs">{{ $collector->machine_name ?? '—' }}</span></p>
        <p><span class="text-gray-500 w-36 inline-block">Display name</span> {{ $collector->name }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Phone</span> {{ $collector->phone ?? '—' }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Machine</span> {{ $collector->machine?->name ?? '—' }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Company</span> {{ $collector->machine?->company?->name ?? '—' }}</p>
        <p><span class="text-gray-500 w-36 inline-block">Status</span>
            {{ ($collector->is_active ?? true) ? 'Active' : 'Inactive' }}
        </p>
    </div>
</x-admin-layout>
