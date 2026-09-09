@props(['type' => 'primary', 'size' => 'md', 'href' => null, 'submit' => false, 'icon' => null])

@php
    $classes = match ($type) {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'outline' => 'btn-outline',
        'danger' => 'btn-danger',
        'success' => 'btn-success',
        default => 'btn-primary',
    };

    if ($size === 'sm') {
        $classes .= ' btn-sm';
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            {!! $icon !!}
        @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $submit ? 'submit' : 'button', 'class' => $classes]) }}>
        @if ($icon)
            {!! $icon !!}
        @endif
        {{ $slot }}
    </button>
@endif