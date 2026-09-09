<x-app-layout>
    <x-slot name="title">Beranda Admin</x-slot>

    <x-ui.page-header title="Ringkasan Admin" :subtitle="'Semua kelas di seluruh platform, ' . Auth::user()->name . '.'">
        <div class="flex flex-col gap-2 sm:flex-row">
            <x-ui.button href="{{ route('users.index') }}" type="outline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                Kelola Akun
            </x-ui.button>
            <x-ui.button href="{{ route('class-rooms.index') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                Semua Kelas
            </x-ui.button>
        </div>
    </x-ui.page-header>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-5">
        <x-ui.stat-card label="Kelas" :value="$classRooms->total()" tone="rose">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg></x-slot>
        </x-ui.stat-card>
        <x-ui.stat-card label="Mata Pelajaran" :value="$totalSubjects" tone="rose-deep">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg></x-slot>
        </x-ui.stat-card>
        <x-ui.stat-card label="Anggota" :value="$totalMembers" tone="green">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg></x-slot>
        </x-ui.stat-card>
        <x-ui.stat-card label="Komting" :value="$totalKomting" tone="amber">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18 18.75c0-2.872-2.686-5.25-6-5.25s-6 2.378-6 5.25v.375c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75z"/></svg></x-slot>
        </x-ui.stat-card>
    </div>

    {{-- Class list --}}
    <x-ui.card title="Semua Kelas">
        <x-slot name="action">
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">{{ $classRooms->total() }} total</span>
                <x-ui.per-page :paginator="$classRooms" />
            </div>
        </x-slot>
        @forelse ($classRooms as $classRoom)
            <a href="{{ route('class-rooms.show', $classRoom) }}" class="group mb-3 flex items-center justify-between gap-3 rounded-md p-3 transition hover:bg-gray-50 last:mb-0">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md brand-gradient text-sm font-bold text-white">
                        {{ Str::limit($classRoom->name, 2, '') }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-gray-900 group-hover:text-rose-800">{{ $classRoom->name }}</p>
                        <div class="mt-0.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500">
                            <span class="badge-rose">{{ $classRoom->code }}</span>
                            <span>{{ $classRoom->members_count }} anggota</span>
                            <span>{{ $classRoom->subjects_count }} mata pelajaran</span>
                        </div>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <span class="hidden text-xs text-gray-400 sm:block">oleh {{ $classRoom->komting?->name ?? 'Tidak Diketahui' }}</span>
                    <svg class="h-5 w-5 shrink-0 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </div>
            </a>
        @empty
            <x-ui.empty-state
                title="Belum ada kelas"
                description="Belum ada kelas di platform.">
                <x-slot name="action">
                    <x-ui.button href="{{ route('class-rooms.create') }}">Buat kelas</x-ui.button>
                </x-slot>
            </x-ui.empty-state>
        @endforelse
        {{ $classRooms->links() }}
    </x-ui.card>
</x-app-layout>
