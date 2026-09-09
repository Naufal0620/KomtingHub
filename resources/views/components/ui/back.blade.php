@props(['href', 'label' => 'Kembali'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => '-ml-2 inline-flex items-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold text-gray-500 transition hover:text-rose-600']) }}>
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    {{ $label }}
</a>