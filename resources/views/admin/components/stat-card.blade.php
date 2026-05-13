@props(['title', 'value', 'delta' => null, 'tone' => 'neutral', 'sub' => null])

@php
    $tones = [
        'blue'    => 'bg-blue-50 text-blue-600',
        'green'   => 'bg-green-50 text-green-600',
        'amber'   => 'bg-amber-50 text-amber-600',
        'neutral' => 'bg-gray-50 text-gray-600',
    ];
    $iconBg = $tones[$tone] ?? $tones['neutral'];
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-5">
    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $title }}</p>
    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $value }}</p>
    @if ($sub)
        <p class="text-xs text-gray-400 mt-1">{{ $sub }}</p>
    @endif
    @if ($delta !== null)
        <p class="text-xs mt-2 {{ str_starts_with((string)$delta, '-') ? 'text-red-500' : 'text-green-600' }}">
            {{ str_starts_with((string)$delta, '-') ? '' : '+' }}{{ $delta }} vs yesterday
        </p>
    @endif
</div>
