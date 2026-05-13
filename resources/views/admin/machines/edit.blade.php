<x-admin-layout :title="'Edit ' . $machine->name">
    <div class="mb-4"><a href="{{ route('admin.machines.show', $machine) }}" class="text-sm text-blue-600 hover:underline">&larr; {{ $machine->name }}</a></div>
    <x-admin.page-header title="Edit Machine" :subtitle="$machine->name" />

    @if ($errors->has('delete'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $errors->first('delete') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.machines.update', $machine) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg">
        @csrf @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="mobile" @selected(old('type', $machine->type)==='mobile')>Mobile</option>
                    <option value="terminal" @selected(old('type', $machine->type)==='terminal')>Terminal</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="active" @selected(old('status', $machine->status)==='active')>Active</option>
                    <option value="inactive" @selected(old('status', $machine->status)==='inactive')>Inactive</option>
                    <option value="maintenance" @selected(old('status', $machine->status)==='maintenance')>Maintenance</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description', $machine->description) }}</textarea>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">Save Changes</button>
        </div>
    </form>
</x-admin-layout>
