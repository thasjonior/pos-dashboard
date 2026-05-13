<x-admin-layout title="New Company">
    <div class="mb-4"><a href="{{ route('admin.companies.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Companies</a></div>
    <x-admin.page-header title="New Company" />

    <form method="POST" action="{{ route('admin.companies.store') }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 max-w-lg"
          x-data="{ slug: '', manual: false }"
          @submit.prevent="$el.submit()">
        @csrf

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   x-on:input="if (!manual) slug = $event.target.value.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'')"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">
                Slug <span class="text-gray-400 font-normal">(auto-generated if blank)</span>
            </label>
            <input type="text" name="slug" value="{{ old('slug') }}"
                   x-model="slug"
                   x-on:input="manual = $event.target.value !== ''"
                   pattern="[a-z0-9\-]+" title="Lowercase letters, numbers and hyphens only"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <p class="text-xs text-gray-400 mt-1">Lowercase, numbers, hyphens only. Difficult to change later.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Location / Address <span class="text-gray-400 font-normal">(optional)</span></label>
            <input type="text" name="location" value="{{ old('location') }}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="precreate_fallback" id="precreate_fallback" value="1"
                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                   {{ old('precreate_fallback') ? 'checked' : '' }}>
            <label for="precreate_fallback" class="text-sm text-gray-700">
                Pre-create <span class="font-mono text-xs">unknown-client-{slug}</span> fallback client
            </label>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">Create Company</button>
        </div>
    </form>
</x-admin-layout>
