@props(['title' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || $action)
        <div class="card-header">
            @if ($title)
                <h3 class="card-title">{{ $title }}</h3>
            @endif
            @if ($action)
                <div class="flex shrink-0 items-center gap-2">{{ $action }}</div>
            @endif
        </div>
    @endif

    <div class="card-body">{{ $slot }}</div>
</div>
