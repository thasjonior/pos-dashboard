<x-admin-layout :title="'Edit ' . $collector->name">
    <div class="mb-4"><a href="{{ route('admin.collectors.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Collectors</a></div>
    <x-admin.page-header title="Edit Collector" :subtitle="$collector->name" />

    <form method="POST" action="{{ route('admin.collectors.update', $collector) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg"
          x-data="{ password: '' }">
        @csrf @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $collector->name) }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Phone <span class="text-red-500">*</span></label>
            <input type="text" name="phone" value="{{ old('phone', $collector->phone) }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Assign Machine</label>
            <select name="machine_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Unassigned</option>
                @foreach ($availableMachines as $m)
                    <option value="{{ $m->id }}" @selected(old('machine_id', $collector->machine?->id) == $m->id)>
                        {{ $m->name }} ({{ $m->company?->slug }})
                    </option>
                @endforeach
            </select>
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
