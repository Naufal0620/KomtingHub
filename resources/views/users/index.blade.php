<x-app-layout>
    <x-slot name="title">Kelola Akun</x-slot>

    <x-ui.page-header title="Kelola Akun" subtitle="Buat dan kelola akun komting serta mahasiswa."
        back-href="{{ route('dashboard') }}" back-label="Beranda">
        <x-ui.button href="{{ route('users.create') }}">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Akun
        </x-ui.button>
    </x-ui.page-header>

    <div class="mb-4 flex flex-wrap gap-2">
        @php
            $tabs = [
                ['label' => 'Semua', 'value' => null, 'count' => $counts['all']],
                ['label' => 'Komting', 'value' => 'komting', 'count' => $counts['komting']],
                ['label' => 'Mahasiswa', 'value' => 'student', 'count' => $counts['student']],
            ];
        @endphp
        @foreach ($tabs as $tab)
            <a href="{{ $tab['value'] ? route('users.index', ['role' => $tab['value']]) : route('users.index') }}"
               class="badge {{ $filter === $tab['value'] ? 'badge-rose-deep' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $tab['label'] }} <span>{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </div>

    <x-ui.card>
        <x-slot name="action">
            <x-ui.per-page :paginator="$users" />
        </x-slot>
        <div class="space-y-2">
            @forelse ($users as $user)
                <div class="flex flex-col gap-2 rounded-md p-2.5 transition hover:bg-gray-50 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md {{ $user->isAdmin() ? 'bg-gray-100 text-gray-500' : 'brand-gradient text-white' }} text-xs font-bold">
                            {{ Str::limit($user->name, 2, '') }}
                        </span>
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-2 font-semibold text-gray-900">
                                {{ $user->name }}
                                @php
                                    $roleBadge = match ($user->role) {
                                        'komting' => 'badge-rose',
                                        'student' => 'badge-blue',
                                        default => 'badge-gray',
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }}">
                                    {{ match ($user->role) { 'komting' => 'Komting', 'student' => 'Mahasiswa', default => 'Admin' } }}
                                </span>
                            </p>
                            <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>

                    @if (! $user->isAdmin())
                        <div class="flex shrink-0 items-center gap-1.5">
                            <a href="{{ route('users.edit', $user) }}" class="flex h-9 w-9 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-700" title="Edit" aria-label="Edit {{ $user->name }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                            </a>
                            <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="Hapus akun {{ $user->name }}?" onsubmit="return confirm(this.dataset.confirm)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus" aria-label="Hapus {{ $user->name }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <x-ui.empty-state title="Belum ada akun" description="Tambahkan akun komting atau mahasiswa baru." />
            @endforelse
        </div>

        <div class="mt-4 border-t border-gray-100 pt-4">
            {{ $users->links() }}
        </div>
    </x-ui.card>
</x-app-layout>