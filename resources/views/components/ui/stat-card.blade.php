@props(['label', 'value', 'tone' => 'rose'])

@php
    $tones = [
        'rose' => 'bg-rose-50 text-rose-700',
        'rose-deep' => 'bg-rose-100 text-rose-800',
        'green' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'red' => 'bg-red-50 text-red-600',
    ];
    $iconBg = $tones[$tone] ?? $tones['rose'];
@endphp

<div {{ $attributes->merge(['class' => 'card p-4 transition hover:shadow-md sm:p-5']) }}>
    <div class="flex items-center gap-3 sm:gap-4">
        @isset($icon)
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md {{ $iconBg }} sm:h-12 sm:w-12">
                {{ $icon }}
            </div>
        @endisset
        <div class="min-w-0">
            <p class="truncate text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</p>
            <p class="mt-0.5 truncate text-xl font-bold text-gray-900 sm:text-2xl">{{ $value }}</p>
        </div>
    </div>
</div>
