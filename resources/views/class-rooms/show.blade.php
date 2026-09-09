<x-app-layout>
    <x-slot name="title">{{ $classRoom->name }}</x-slot>

    @php($canManage = auth()->user()->can('manage', $classRoom))

    {{-- Header / hero --}}
    <div class="card mb-6 overflow-hidden">
        <div class="p-5 sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-md brand-gradient text-lg font-bold text-white shadow-sm">
                    {{ Str::limit($classRoom->name, 2, '') }}
                </span>
                <div class="min-w-0">
                    <x-ui.back :href="route('class-rooms.index')" label="Ruang Kelas" class="mb-2" />
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="page-title">{{ $classRoom->name }}</h1>
                        @if ($classRoom->code)
                            <span class="badge-rose">{{ $classRoom->code }}</span>
                        @endif
                    </div>
                    @if ($classRoom->description)
                        <p class="mt-1 text-sm text-gray-600">{{ $classRoom->description }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-400">
                        Komting: <span class="font-medium text-gray-600">{{ $classRoom->komting?->name ?? 'Belum ditugaskan' }}</span>
                        @if ($classRoom->komting?->email)
                            · {{ $classRoom->komting->email }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if (auth()->user()->isAdmin())
                    <x-ui.button href="{{ route('class-rooms.edit', $classRoom) }}" type="secondary" size="sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                        Ubah
                    </x-ui.button>
                @endif
                @if ($canManage)
                    <x-ui.button href="{{ route('class-rooms.members.index', $classRoom) }}" type="secondary" size="sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                        Anggota
                    </x-ui.button>
                @endif
            </div>
        </div>

        {{-- Stat bar --}}
        <div class="mt-5 grid grid-cols-3 gap-3 border-t border-gray-100 pt-5">
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $classRoom->subjects->count() }}</p>
                <p class="text-xs text-gray-500">Mata Pelajaran</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $members->total() }}</p>
                <p class="text-xs text-gray-500">Anggota</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $totalGroups }}</p>
                <p class="text-xs text-gray-500">Kelompok</p>
            </div>
        </div>
    </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Subjects --}}
        <x-ui.card :title="'Mata Pelajaran ('.$classRoom->subjects->count().')'" class="lg:col-span-2">
            @if ($canManage)
                <x-slot name="action">
                    <x-ui.button href="{{ route('class-rooms.subjects.create', $classRoom) }}" type="outline" size="sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah
                    </x-ui.button>
                </x-slot>
            @endif

            <div>
                @forelse ($classRoom->subjects as $subject)
                    <a href="{{ route('class-rooms.subjects.show', [$classRoom, $subject]) }}" class="group mb-3 flex items-center justify-between gap-3 rounded-md border border-gray-100 p-3 transition hover:border-rose-100 hover:bg-rose-50/50 last:mb-0">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-md bg-rose-50 text-sm font-bold text-rose-700">{{ Str::limit($subject->name, 2, '') }}</span>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $subject->name }}</p>
                                <p class="text-xs text-gray-500">{{ $subject->code }} · {{ $subject->group_mode === 'random' ? 'Acak Otomatis' : 'Pemilihan Mandiri' }}</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-gray-300 group-hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                @empty
                    <x-ui.empty-state title="Belum ada mata pelajaran" description="Tambah mata pelajaran untuk mengelola kelompok dan tugas." />
                @endforelse
            </div>
        </x-ui.card>

        {{-- Members (sidebar) --}}
        <x-ui.card :title="'Anggota ('.$members->total().')'" class="lg:col-span-1">
            <x-slot name="action">
                <x-ui.per-page :paginator="$members" />
            </x-slot>
            <div class="space-y-2.5">
                @forelse ($members as $member)
                    <div class="flex items-center gap-3 border-b border-gray-50 pb-2.5 last:border-0">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-bold text-gray-600">{{ Str::limit($member->name, 2, '') }}</span>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-gray-900">{{ $member->name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $member->email }}</p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state title="Belum ada anggota" description="Tambahkan mahasiswa ke kelas ini." />
                @endforelse
            </div>

            <div class="mt-4 border-t border-gray-100 pt-4">
                    {{ $members->links() }}
                </div>
        </x-ui.card>
    </div>
</x-app-layout>