<x-app-layout>
    <x-slot name="title">Kelompok</x-slot>

    @php($canManage = auth()->user()->can('manage', $subject))
    @php($isLocked = (bool) $subject->groups_locked)

    <x-ui.page-header :title="'Kelompok · '.$subject->name" :subtitle="$subject->code"
        :back-href="route('class-rooms.subjects.show', [$classRoom, $subject])" :back-label="$subject->name">
        @if ($canManage)
            @if ($isLocked)
                <form method="POST" action="{{ route('class-rooms.subjects.groups.unlock', [$classRoom, $subject]) }}" data-confirm="Buka kunci kelompok? Anggota dapat kembali bergabung atau keluar." onsubmit="return confirm(this.dataset.confirm)">
                    @csrf
                    <x-ui.button :submit="true" type="success" size="sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        Buka Kunci Kelompok
                    </x-ui.button>
                </form>
            @else
                <x-ui.button href="{{ route('class-rooms.subjects.export.groups', [$classRoom, $subject]) }}" type="outline" size="sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Ekspor
                </x-ui.button>
                <x-ui.button href="{{ route('class-rooms.subjects.groups.create', [$classRoom, $subject]) }}" size="sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Kelompok
                </x-ui.button>
                <form method="POST" action="{{ route('class-rooms.subjects.groups.lock', [$classRoom, $subject]) }}" data-confirm="Kunci kelompok? Anggota tidak dapat lagi bergabung atau keluar." onsubmit="return confirm(this.dataset.confirm)">
                    @csrf
                    <x-ui.button :submit="true" size="sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        Kunci Kelompok
                    </x-ui.button>
                </form>
            @endif
        @endif
    </x-ui.page-header>

    {{-- Status banner --}}
    <div class="mb-6 flex items-center justify-between rounded-lg border p-4 {{ $isLocked ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }}">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-md {{ $isLocked ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                @if ($isLocked)
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                @else
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                @endif
            </span>
            <div>
                    <p class="text-sm font-semibold {{ $isLocked ? 'text-amber-800' : 'text-emerald-800' }}">
                    {{ $isLocked ? 'Kelompok terkunci' : 'Kelompok terbuka' }}
                </p>
                <p class="text-xs {{ $isLocked ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $subject->group_mode === 'random' ? 'Mata pelajaran diacak otomatis' : 'Mata pelajaran pilihan sendiri' }}
                    @if (! $isLocked && $subject->isSelectMode())
                        · anggota masih dapat bergabung atau keluar dari kelompoknya
                    @endif
                </p>
            </div>
        </div>
        <span class="badge-{{ $isLocked ? 'amber' : 'green' }} hidden sm:inline-flex">{{ $isLocked ? 'Terkunci' : 'Terbuka' }}</span>
    </div>

    {{-- Groups grid --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="text-sm text-gray-500">{{ $groups->total() }} kelompok</p>
        <x-ui.per-page :paginator="$groups" />
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($groups as $group)
            @php($inGroup = $group->members->contains(auth()->id()))
            <a href="{{ route('class-rooms.subjects.groups.show', [$classRoom, $subject, $group]) }}"
               class="group relative overflow-hidden rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-900/5 transition hover:shadow-md">
                <div class="absolute inset-x-0 top-0 h-1 {{ $inGroup ? 'bg-emerald-500' : 'brand-gradient' }}"></div>
                <div class="flex items-start justify-between">
                    <span class="flex h-12 w-12 items-center justify-center rounded-md {{ $inGroup ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-700' }} text-base font-bold">
                        {{ Str::limit($group->name, 2, '') }}
                    </span>
                    @if ($inGroup)
                        <span class="badge-green">Kamu</span>
                    @endif
                </div>
                <h3 class="mt-3 font-bold text-gray-900">{{ $group->name }}</h3>
                <p class="text-sm text-gray-500">{{ $group->members->count() }} {{ Str::plural('anggota', $group->members->count()) }}</p>
            </a>
        @empty
            <div class="col-span-full">
                <x-ui.empty-state title="Belum ada kelompok yang dibentuk"
                    description="{{ $subject->group_mode === 'random' ? 'Jalankan pengacakan yang transparan untuk membentuk kelompok secara otomatis.' : 'Buat kelompok dan biarkan anggota bergabung, atau jalankan pengacakan.' }}">
                    @if ($canManage)
                        @if ($subject->group_mode === 'random')
                            <x-slot name="action">
                                <x-ui.button href="{{ route('class-rooms.subjects.shuffle.create', [$classRoom, $subject]) }}">Jalankan pengacakan</x-ui.button>
                            </x-slot>
                        @else
                            <x-slot name="action">
                                <x-ui.button href="{{ route('class-rooms.subjects.groups.create', [$classRoom, $subject]) }}">Buat kelompok</x-ui.button>
                            </x-slot>
                        @endif
                    @endif
                </x-ui.empty-state>
            </div>
        @endforelse
    </div>

    <div class="mt-6 border-t border-gray-100 pt-4">
        {{ $groups->links() }}
    </div>
</x-app-layout>
