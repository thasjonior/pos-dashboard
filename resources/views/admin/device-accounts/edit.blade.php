<x-admin-layout :title="'Edit ' . ($collector->machine_name ?? $collector->name)">
    <div class="mb-4"><a href="{{ route('admin.device-accounts.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Device accounts</a></div>
    <x-admin.page-header title="Edit device account" :subtitle="$collector->machine_name ?? $collector->name" />

    <form method="POST" action="{{ route('admin.device-accounts.update', $collector) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg"
          x-data="{ password: '' }">
        @csrf @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Machine name (login identity)</label>
            <input type="text" value="{{ $collector->machine_name ?? '—' }}" disabled
                   class="w-full border border-gray-100 bg-gray-50 rounded-lg px-3 py-2 text-sm font-mono text-gray-500 cursor-not-allowed">
            <p class="text-xs text-gray-400 mt-1">Login identity — not editable here. Reassign via machine settings.</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Display name (shown on receipts) <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $collector->name) }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Phone <span class="text-gray-400 font-normal">(optional)</span></label>
            <input type="text" name="phone" value="{{ old('phone', $collector->phone) }}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">New Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
            <div class="flex gap-2">
                <input type="text" name="password" x-model="password"
                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button type="button" @click="password = Array.from({length:12},()=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNP23456789!@#'[Math.floor(Math.random()*55)]).join('')"
                        class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition">Generate</button>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">Save</button>
        </div>
    </form>
</x-admin-layout>
