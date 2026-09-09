<x-app-layout>
    <x-slot name="title">Kelas</x-slot>

    <x-ui.page-header :title="auth()->user()->isAdmin() ? 'Ruang Kelas' : 'Kelas yang Kamu Kelola'"
        :subtitle="auth()->user()->isAdmin() ? 'Tetapkan kelas dan tugaskan kepada komting.' : 'Kelas yang ditugaskan oleh admin untuk kamu kelola.'"
        back-href="{{ route('dashboard') }}" back-label="Dashboard">
        @if (auth()->user()->isAdmin())
            <x-ui.button href="{{ route('class-rooms.create') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Kelas Baru
            </x-ui.button>
        @endif
    </x-ui.page-header>

    <x-ui.card>
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm text-gray-500">{{ $classRooms->total() }} kelas</p>
            <x-ui.per-page :paginator="$classRooms" />
        </div>

        <div class="space-y-3">
            @forelse ($classRooms as $classRoom)
                <a href="{{ route('class-rooms.show', $classRoom) }}" class="group flex items-center justify-between gap-3 rounded-md border border-transparent bg-white p-4 shadow-sm ring-1 ring-gray-900/5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md brand-gradient text-sm font-bold text-white">
                            {{ Str::limit($classRoom->name, 2, '') }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900">{{ $classRoom->name }}</p>
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                <span class="badge-rose">{{ $classRoom->code }}</span>
                                @if (auth()->user()->isAdmin())
                                    <span>Komting: {{ $classRoom->komting?->name ?? 'Belum ditugaskan' }}</span>
                                    <span>·</span>
                                @endif
                                <span>{{ $classRoom->members_count }} anggota</span>
                                <span>·</span>
                                <span>{{ $classRoom->subjects_count }} mata pelajaran</span>
                            </p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 shrink-0 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            @empty
                @if (auth()->user()->isAdmin())
                    <x-ui.empty-state
                        title="Belum ada ruang kelas"
                        description="Buat ruang kelas pertama lalu tugaskan kepada komting untuk mulai mengelola.">
                        <x-slot name="action">
                            <x-ui.button href="{{ route('class-rooms.create') }}">Buat kelas</x-ui.button>
                        </x-slot>
                    </x-ui.empty-state>
                @else
                    <x-ui.empty-state
                        title="Belum ada kelas yang ditugaskan"
                        description="Admin belum menugaskan ruang kelas kepadamu. Hubungi admin untuk mendapatkan akses." />
                @endif
            @endforelse
        </div>

        <div class="mt-5 border-t border-gray-100 pt-4">
            {{ $classRooms->links() }}
        </div>
    </x-ui.card>
</x-app-layout>
