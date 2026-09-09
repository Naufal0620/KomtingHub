@php
    $perPageCurrent = request()->query('per_page', null);
@endphp
<div class="flex flex-col items-center justify-between gap-4 sm:flex-row sm:items-center">

    <div class="flex flex-1 flex-wrap items-center justify-center gap-3 sm:justify-start">
        @if ($paginator->total() > 0)
            <p class="text-sm text-gray-500">
                Menampilkan <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span>
                sampai <span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span> hasil
            </p>
        @endif
    </div>

    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Halaman sebelumnya" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-gray-50 text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="px-1.5 text-sm font-medium text-gray-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" aria-label="Halaman {{ $page }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-rose-800 bg-rose-800 px-3 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-gray-200 bg-white px-3 text-sm font-medium text-gray-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="Halaman berikutnya" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-gray-50 text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </span>
            @endif
        </nav>
    @endif
</div>