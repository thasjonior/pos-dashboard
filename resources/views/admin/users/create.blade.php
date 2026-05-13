<x-admin-layout title="New Admin User">
    <div class="mb-4"><a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Users</a></div>
    <x-admin.page-header title="New Admin User" />

    <form method="POST" action="{{ route('admin.users.store') }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg"
          x-data="{ password: '' }">
        @csrf

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Role <span class="text-red-500">*</span></label>
            <select name="role" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="admin"       @selected(old('role') === 'admin')>admin</option>
                <option value="super_admin" @selected(old('role') === 'super_admin')>super_admin</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
                <input type="text" name="password" x-model="password" required
                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button type="button"
                        @click="password = Array.from({length:12},()=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNP23456789!@#'[Math.floor(Math.random()*55)]).join('')"
                        class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition">Generate</button>
            </div>
            <p class="text-xs text-amber-600 mt-1">Copy this password — it won't be shown again after creation.</p>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">Create User</button>
        </div>
    </form>
</x-admin-layout>
