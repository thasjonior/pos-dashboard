<x-admin-layout :title="'Edit ' . $user->name">
    <div class="mb-4"><a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Users</a></div>
    <x-admin.page-header title="Edit User" :subtitle="$user->name" />

    <form method="POST" action="{{ route('admin.users.update', $user) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg">
        @csrf @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Role <span class="text-red-500">*</span></label>
            <select name="role" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    @if ($user->id === auth()->id() && $user->isSuperAdmin()) disabled @endif>
                <option value="admin"       @selected(old('role', $user->role) === 'admin')>admin</option>
                <option value="super_admin" @selected(old('role', $user->role) === 'super_admin')>super_admin</option>
            </select>
            @if ($user->id === auth()->id() && $user->isSuperAdmin())
                <input type="hidden" name="role" value="super_admin">
                <p class="text-xs text-gray-400 mt-1">You cannot demote yourself.</p>
            @endif
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">Save Changes</button>
        </div>
    </form>
</x-admin-layout>
