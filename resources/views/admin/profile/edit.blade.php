<x-admin-layout title="My Profile">
    <x-admin.page-header title="My Profile" />

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <!-- Profile Info -->
    <form method="POST" action="{{ route('admin.profile.update') }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg mb-6">
        @csrf @method('PATCH')

        @if ($errors->hasAny(['name', 'email']))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                @foreach ($errors->only(['name', 'email']) as $err)<p>{{ $err }}</p>@endforeach
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
        <div class="text-xs text-gray-400">
            Role: <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded">{{ $user->role }}</span>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">Save Profile</button>
        </div>
    </form>

    <!-- Change Password -->
    <form method="POST" action="{{ route('admin.profile.password') }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg">
        @csrf @method('PUT')

        <h3 class="text-sm font-semibold text-gray-700">Change Password</h3>

        @if ($errors->hasAny(['current_password', 'password']))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                @foreach ($errors->only(['current_password', 'password']) as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Current Password</label>
            <input type="password" name="current_password" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">New Password</label>
            <input type="password" name="password" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Confirm New Password</label>
            <input type="password" name="password_confirmation" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-6 py-2 rounded-lg transition">Change Password</button>
        </div>
    </form>
</x-admin-layout>
