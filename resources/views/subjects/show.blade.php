<x-app-layout>
    <x-slot name="title">{{ $subject->name }}</x-slot>

    @php($canManage = auth()->user()->can('manage', $subject))

    {{-- Header / hero --}}
    <div class="card mb-6 overflow-hidden">
        <div class="p-5 sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-md bg-rose-50 text-lg font-bold text-rose-700 shadow-sm">
                    {{ Str::limit($subject->name, 2, '') }}
                </span>
                <div class="min-w-0">
                    <x-ui.back :href="route('class-rooms.show', $classRoom)" :label="$classRoom->name" class="mb-2" />
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="page-title">{{ $subject->name }}</h1>
                        @if ($subject->code)
                            <span class="badge-rose">{{ $subject->code }}</span>
                        @endif
                        <span class="badge-gray">{{ $subject->group_mode === 'random' ? 'Acak Otomatis' : 'Pemilihan Mandiri' }}</span>
                    </div>
                    @if ($subject->description)
                        <p class="mt-1 text-sm text-gray-600">{{ $subject->description }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-400">
                        Kelas: <span class="font-medium text-gray-600">{{ $classRoom->name }}</span>
                        @if ($classRoom->code) · {{ $classRoom->code }} @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($canManage)
                    <x-ui.button href="{{ route('class-rooms.subjects.edit', [$classRoom, $subject]) }}" type="secondary" size="sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                        Ubah
                    </x-ui.button>
                    <x-ui.button href="{{ route('class-rooms.subjects.members.index', [$classRoom, $subject]) }}" type="secondary" size="sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                        Anggota
                    </x-ui.button>
                @endif
            </div>
        </div>

        {{-- Stat bar --}}
        <div class="mt-5 grid grid-cols-3 gap-3 border-t border-gray-100 pt-5">
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $subject->members->count() }}</p>
                <p class="text-xs text-gray-500">Anggota</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $subject->groups->count() }}</p>
                <p class="text-xs text-gray-500">Kelompok</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $subject->assignments->count() }}</p>
                <p class="text-xs text-gray-500">Tugas</p>
            </div>
        </div>
    </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Assignments (main) --}}
        <x-ui.card :title="'Tugas ('.$subject->assignments->count().')'" class="lg:col-span-2">
            <x-slot name="action">
                <a href="{{ route('class-rooms.subjects.assignments.index', [$classRoom, $subject]) }}" class="text-sm font-medium text-rose-700 hover:underline">Lihat semua</a>
            </x-slot>
            <div class="space-y-3">
                @forelse ($subject->assignments as $assignment)
                    <a href="{{ route('class-rooms.subjects.assignments.show', [$classRoom, $subject, $assignment]) }}" class="group flex items-center justify-between rounded-md border border-gray-100 p-3 transition hover:border-rose-100 hover:bg-rose-50/40">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-rose-50 text-sm font-bold text-rose-700">{{ Str::limit($assignment->title, 2, '') }}</span>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $assignment->title }}</p>
                                <p class="text-xs text-gray-500">{{ $assignment->type === 'group' ? 'Kelompok' : 'Individu' }}</p>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-gray-300 group-hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                @empty
                    <x-ui.empty-state title="Belum ada tugas" description="Tugas memungkinkan kamu melacak dan memberi nilai per anggota." />
                @endforelse
            </div>
        </x-ui.card>

        {{-- Groups (sidebar) --}}
        <x-ui.card :title="'Kelompok ('.$subject->groups->count().')'" class="lg:col-span-1">
            <x-slot name="action">
                <a href="{{ route('class-rooms.subjects.groups.index', [$classRoom, $subject]) }}" class="text-sm font-medium text-rose-700 hover:underline">Lihat semua</a>
            </x-slot>
            <div class="space-y-3">
                @forelse ($subject->groups as $group)
                    <a href="{{ route('class-rooms.subjects.groups.show', [$classRoom, $subject, $group]) }}" class="group flex items-center justify-between rounded-md border border-gray-100 p-3 transition hover:border-rose-100 hover:bg-rose-50/40">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-50 text-sm font-bold text-emerald-600">{{ Str::limit($group->name, 2, '') }}</span>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $group->name }}</p>
                                <p class="text-xs text-gray-500">{{ $group->members->count() }} anggota</p>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-gray-300 group-hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                @empty
                    <x-ui.empty-state title="Belum ada kelompok" description="Kelompok akan dibentuk saat dibuat atau diacak." />
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-app-layout>