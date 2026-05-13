<x-admin-layout title="Companies">
    <x-admin.page-header title="Companies">
        <x-slot name="actions">
            <a href="{{ route('admin.companies.create') }}"
               class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">+ New Company</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @error('delete')
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $message }}</div>
    @enderror

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if ($companies->isEmpty())
            <x-admin.empty-state title="No companies yet" />
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Slug</th>
                        <th class="px-5 py-3 text-center">Machines</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($companies as $company)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $company->name }}</td>
                            <td class="px-5 py-3"><span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $company->slug }}</span></td>
                            <td class="px-5 py-3 text-center text-gray-600">{{ $company->machines_count }}</td>
                            <td class="px-5 py-3 text-right space-x-3">
                                <a href="{{ route('admin.companies.edit', $company) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" class="inline"
                                      onsubmit="return confirm('Delete {{ addslashes($company->name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-admin-layout>
