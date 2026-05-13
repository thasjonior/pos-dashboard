@props(['id', 'title'])

<div x-data="{ open: false }" id="{{ $id }}-wrapper">
    <!-- Trigger slot -->
    <span @click="open = true">{{ $trigger ?? '' }}</span>

    <!-- Backdrop + modal -->
    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         @keydown.escape.window="open = false">
        <div @click.outside="open = false"
             class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-1">{{ $title }}</h3>
            {{ $slot }}
        </div>
    </div>
</div>
