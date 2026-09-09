<x-app-layout>
    <x-slot name="title">Mata Pelajaran</x-slot>

    @php($canManage = auth()->user()->can('manage', $classRoom))

    <x-ui.page-header :title="'Mata Pelajaran · '.$classRoom->name" :subtitle="$classRoom->code"
        :back-href="route('class-rooms.show', $classRoom)" :back-label="$classRoom->name">
        @if ($canManage)
            <x-ui.button href="{{ route('class-rooms.subjects.create', $classRoom) }}" size="sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah Mata Pelajaran
            </x-ui.button>
        @endif
    </x-ui.page-header>

    <x-ui.card>
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm text-gray-500">{{ $subjects->total() }} mata pelajaran</p>
            <x-ui.per-page :paginator="$subjects" />
        </div>

        <div class="space-y-3">
            @forelse ($subjects as $subject)
                <a href="{{ route('class-rooms.subjects.show', [$classRoom, $subject]) }}" class="group flex items-center justify-between gap-3 rounded-md bg-white p-4 shadow-sm ring-1 ring-gray-900/5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-rose-50 text-sm font-bold text-rose-700">{{ Str::limit($subject->name, 2, '') }}</span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="truncate font-semibold text-gray-900">{{ $subject->name }}</p>
                                <span class="badge-rose shrink-0">{{ $subject->code }}</span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                                <span class="badge-{{ $subject->group_mode === 'random' ? 'rose-deep' : 'amber' }}">{{ $subject->group_mode === 'random' ? 'Acak Otomatis' : 'Pilih Sendiri' }}</span>
                                <span>{{ $subject->members_count }} anggota</span>
                                <span>{{ $subject->groups_count }} kelompok</span>
                                <span>{{ $subject->assignments_count }} tugas</span>
                            </div>
                        </div>
                    </div>
                    <svg class="h-5 w-5 shrink-0 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            @empty
                <x-ui.empty-state title="Belum ada mata pelajaran" description="Tambah mata pelajaran ke kelas ini untuk mengelola kelompok dan tugas.">
                    @if ($canManage)
                        <x-slot name="action">
                            <x-ui.button href="{{ route('class-rooms.subjects.create', $classRoom) }}">Tambah Mata Pelajaran</x-ui.button>
                        </x-slot>
                    @endif
                </x-ui.empty-state>
            @endforelse
        </div>

        <div class="mt-5 border-t border-gray-100 pt-4">
            {{ $subjects->links() }}
        </div>
    </x-ui.card>
</x-app-layout>
