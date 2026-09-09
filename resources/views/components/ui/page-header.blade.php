@props(['title', 'subtitle' => null, 'backHref' => null, 'backLabel' => 'Kembali'])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div>
        @if ($backHref)
            <x-ui.back :href="$backHref" :label="$backLabel" class="mb-2" />
        @endif
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-2">
        {{ $slot }}
    </div>
</div>
