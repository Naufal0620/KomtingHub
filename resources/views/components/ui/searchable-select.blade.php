@props([
    'name' => null,
    'id' => null,
    'options' => [],
    'selected' => [],
    'multiple' => false,
    'placeholder' => 'Pilih...',
    'required' => false,
])

@php
    $fieldId = $id ?? (string) Str::of($name)->replaceMatches('/[^a-z0-9_]/i', '_')->slug('_');
    $optionList = collect($options)
        ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
        ->values()
        ->all();
    $selectedList = collect($selected)
        ->map(fn ($value) => (string) $value)
        ->values()
        ->all();
@endphp

<div
    x-data="searchableSelect({ multiple: @js($multiple), options: @js($optionList), selected: @js($selectedList), placeholder: @js($placeholder) })"
    class="relative"
>
    {{-- Hidden fields submitted with the form --}}
    @if ($multiple)
        <template x-for="value in selected" :key="value">
            <input type="hidden" :name="'{{ $name }}[]'" :value="value">
        </template>
    @else
        <input type="hidden" :name="'{{ $name }}'" :value="selected[0] ?? ''">
    @endif

    {{-- Trigger button --}}
    <button
        type="button"
        @click="toggle"
        @keydown.esc="close"
        :aria-expanded="open"
        class="flex min-h-[44px] w-full items-center justify-between gap-2 rounded-md border-0 bg-white px-3.5 py-2 text-left text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 transition focus:outline-none focus:ring-2 focus:ring-rose-600"
    >
        <span class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
            <template x-if="isSelectedEmpty">
                <span class="text-gray-400" x-text="placeholder"></span>
            </template>

            {{-- Single select: show chosen label --}}
            <template x-if="!multiple && !isSelectedEmpty">
                <span class="truncate font-medium" x-text="selectedLabels[0]"></span>
            </template>

            {{-- Multiple select: show chosen chips --}}
            <template x-if="multiple">
                <template x-for="(value, index) in selected" :key="value">
                    <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-800">
                        <span x-text="labelOf(value)"></span>
                        <span @click.stop="selected.splice(index, 1)" class="cursor-pointer text-rose-500 font-bold hover:text-rose-700">&times;</span>
                    </span>
                </template>
            </template>
        </span>

        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        @click.outside="close"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="absolute z-20 mt-1.5 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg"
    >
        <div class="border-b border-gray-100 p-2">
            <div class="relative">
                <svg class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input
                    x-ref="search"
                    type="text"
                    x-model="query"
                    placeholder="Cari…"
                    class="w-full rounded-md border-0 bg-gray-50 py-2 pl-8 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-600"
                />
            </div>
        </div>

        <ul class="max-h-56 overflow-y-auto py-1">
            <template x-for="opt in filteredOptions" :key="opt.value">
                <li>
                    <button
                        type="button"
                        @click="select(opt.value)"
                        class="flex w-full items-center justify-between gap-2 px-3.5 py-2 text-left text-sm text-gray-700 transition hover:bg-rose-50"
                    >
                        <span x-text="opt.label"></span>
                        <svg x-show="isSelected(opt.value)" class="h-4 w-4 shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                    </button>
                </li>
            </template>

            <li x-show="filteredOptions.length === 0" class="px-3.5 py-6 text-center text-sm text-gray-400">Tidak ada yang cocok</li>
        </ul>
    </div>
</div>