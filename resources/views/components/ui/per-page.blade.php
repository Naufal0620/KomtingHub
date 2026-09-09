@props(['paginator'])

@php
    $perPageCurrent = request()->query('per_page', null);
    $perPageAllowed = [5, 10, 15, 25, 50, 100];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-1.5']) }}>
    <span class="text-xs text-gray-400">Tampilkan</span>
    <form method="get" class="flex items-center">
        @foreach (request()->except(['per_page', 'page']) as $key => $value)
            @if (is_array($value))
                @foreach ($value as $subKey => $subValue)
                    <input type="hidden" name="{{ $key . '[' . $subKey . ']' }}" value="{{ $subValue }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <select name="per_page" onchange="this.form.submit()"
                class="min-h-[32px] cursor-pointer rounded-md border border-gray-200 bg-white py-1.5 pl-2 pr-7 text-xs font-medium text-gray-700 focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">
            @foreach ($perPageAllowed as $per)
                <option value="{{ $per }}" @selected((string) $per === (string) $perPageCurrent)>{{ $per }}</option>
            @endforeach
            <option value="all" @selected($perPageCurrent === 'all')>Semua</option>
        </select>
    </form>
</div>