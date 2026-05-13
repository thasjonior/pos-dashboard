<x-admin-layout :title="'Edit ' . $company->name">
    <div class="mb-4"><a href="{{ route('admin.companies.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Companies</a></div>
    <x-admin.page-header title="Edit Company" :subtitle="$company->name" />

    <form method="POST" action="{{ route('admin.companies.update', $company) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg">
        @csrf @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Slug <span class="text-red-500">*</span></label>
            <input type="text" name="slug" value="{{ old('slug', $company->slug) }}" required
                   pattern="[a-z0-9\-]+" title="Lowercase letters, numbers and hyphens only"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <p class="text-xs text-amber-600 mt-1">Warning: changing the slug will break fallback client names and dashboard keys.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Location / Address <span class="text-gray-400 font-normal">(optional)</span></label>
            <input type="text" name="location" value="{{ old('location', $company->location ?? '') }}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">Save Changes</button>
        </div>
    </form>
</x-admin-layout>
