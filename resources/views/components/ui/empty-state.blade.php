@props(['title', 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 p-8 text-center']) }}>
    @if ($icon)
        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-md bg-gray-100 text-gray-400">
            {{ $icon }}
        </div>
    @endif
    <p class="text-sm font-semibold text-gray-700">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $description }}</p>
    @endif
    @if (isset($action) && ! empty(trim((string) $action)))
        <div class="mt-4">{{ $action }}</div>
    @endif
</div>
