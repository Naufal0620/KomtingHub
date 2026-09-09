<x-app-layout>
    <x-slot name="title">Kelola Anggota</x-slot>

<x-ui.page-header title="Kelola Anggota" :subtitle="$subject->name"
        :back-href="route('class-rooms.subjects.show', [$classRoom, $subject])" :back-label="$subject->name">
</x-ui.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card :title="'Anggota Terdaftar ('.$members->total().')'">
            <x-slot name="action">
                <x-ui.per-page :paginator="$members" />
            </x-slot>
            <div class="space-y-2">
                @forelse ($members as $member)
                    <div class="flex items-center justify-between gap-3 rounded-md p-2.5 transition hover:bg-gray-50">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-bold text-gray-600">{{ Str::limit($member->name, 2, '') }}</span>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-900">{{ $member->name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $member->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('class-rooms.subjects.members.destroy', [$classRoom, $subject, $member]) }}" data-confirm="Hapus {{ $member->name }} dari mata pelajaran ini?" onsubmit="return confirm(this.dataset.confirm)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" aria-label="Hapus {{ $member->name }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-red-600 transition hover:bg-red-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <x-ui.empty-state title="Belum ada anggota terdaftar" description="Daftarkan anggota kelas menggunakan formulir." />
                @endforelse
            </div>

            <div class="mt-4 border-t border-gray-100 pt-4">
                {{ $members->links() }}
            </div>
        </x-ui.card>

        <x-ui.card title="Daftarkan Mahasiswa">
            <form method="POST" action="{{ route('class-rooms.subjects.members.store', [$classRoom, $subject]) }}">
                @csrf
                <label for="user_ids" class="label">Anggota Kelas</label>
                <x-ui.searchable-select
                    name="user_ids"
                    placeholder="Pilih anggota kelas…"
                    multiple
                    :options="$availableUsers->mapWithKeys(fn ($u) => [$u->id => $u->name.' ('.$u->email.')'])"
                />
                @error('user_ids')<p class="form-error">{{ $message }}</p>@enderror

                <div class="mt-4">
                    <x-ui.button :submit="true" class="w-full sm:w-auto">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Daftarkan yang Dipilih
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
