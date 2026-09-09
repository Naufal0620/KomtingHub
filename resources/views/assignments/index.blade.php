<x-app-layout>
    <x-slot name="title">Tugas</x-slot>

    @php($canManage = auth()->user()->can('manage', $subject))

    <x-ui.page-header :title="'Tugas · '.$subject->name" :subtitle="$subject->code"
        :back-href="route('class-rooms.subjects.show', [$classRoom, $subject])" :back-label="$subject->name">
        @if ($canManage)
            <x-ui.button href="{{ route('class-rooms.subjects.export.assignments', [$classRoom, $subject]) }}" type="outline" size="sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Ekspor
            </x-ui.button>
            <x-ui.button href="{{ route('class-rooms.subjects.assignments.create', [$classRoom, $subject]) }}" size="sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tugas Baru
            </x-ui.button>
        @endif
    </x-ui.page-header>

    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="text-sm text-gray-500">{{ $assignments->total() }} tugas</p>
        <x-ui.per-page :paginator="$assignments" />
    </div>

    <div class="space-y-3">
        @forelse ($assignments as $assignment)
            @php($progress = Auth::user()->isStudent() ? $assignment->users->where('id', Auth::id())->first()?->pivot : null)
            <a href="{{ route('class-rooms.subjects.assignments.show', [$classRoom, $subject, $assignment]) }}"
               class="group flex flex-col gap-3 rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-900/5 transition hover:shadow-md sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-rose-50 text-sm font-bold text-rose-700">{{ Str::limit($assignment->title, 2, '') }}</span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold text-gray-900">{{ $assignment->title }}</p>
                            <span class="badge-gray">{{ $assignment->type === 'group' ? 'Kelompok' : 'Individu' }}</span>
                            @if ($assignment->requiresFile())
                                <span class="badge-blue">Berkas</span>
                            @endif
                            @if ($assignment->requiresLink())
                                <span class="badge-blue">Tautan</span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500">
                            @if ($assignment->due_date)
                                <span>Tenggat: {{ $assignment->due_date->format('M j, Y') }}</span>
                            @endif
                            @if ($canManage)
                                <span>{{ $assignment->users->count() }} anggota</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 sm:justify-end">
                    @if ($progress)
                        <span class="badge-{{ in_array($progress->status, ['done', 'graded']) ? 'green' : 'amber' }}">
                            {{ match ($progress->status) { 'graded' => 'Dinilai', 'done' => 'Selesai', default => 'Tertunda' } }}
                            @if ($progress->grade)
                                · {{ $progress->grade }}
                            @endif
                        </span>
                    @endif
                    <svg class="h-5 w-5 shrink-0 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </div>
            </a>
        @empty
            <x-ui.empty-state title="Belum ada tugas" description="Buat tugas untuk mulai melacak dan menilai kemajuan.">
                @if ($canManage)
                    <x-slot name="action">
                        <x-ui.button href="{{ route('class-rooms.subjects.assignments.create', [$classRoom, $subject]) }}">Buat tugas</x-ui.button>
                    </x-slot>
                @endif
            </x-ui.empty-state>
        @endforelse
    </div>

    <div class="mt-6 border-t border-gray-100 pt-4">
        {{ $assignments->links() }}
    </div>
</x-app-layout>
