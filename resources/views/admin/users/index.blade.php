<x-admin-layout title="Users">
    <x-admin.page-header title="Admin Users">
        <x-slot name="actions">
            <a href="{{ route('admin.users.create') }}"
               class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">+ New User</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if ($users->isEmpty())
            <x-admin.empty-state title="No admin users found" />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50 {{ $user->id === auth()->id() ? 'bg-blue-50/40' : '' }}">
                            <td class="px-5 py-3 font-medium">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="text-xs text-blue-400 ml-1">(you)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $user->role }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($user->is_active ?? true)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right space-x-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                                @if ($user->id !== auth()->id() && ($user->is_active ?? true))
                                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="inline"
                                          onsubmit="return confirm('Deactivate {{ addslashes($user->name) }}?')">
                                        @csrf
                                        <button type="submit" class="text-amber-500 hover:underline text-xs">Deactivate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-admin-layout>
