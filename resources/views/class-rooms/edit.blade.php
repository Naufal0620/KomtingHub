<x-app-layout>
    <x-slot name="title">Ubah Kelas</x-slot>

    <x-ui.page-header title="Ubah Ruang Kelas" :subtitle="$classRoom->name"
        :back-href="route('class-rooms.show', $classRoom)" :back-label="$classRoom->name" />

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('class-rooms.update', $classRoom) }}" class="card mb-6 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="label">Nama</label>
                <input id="name" name="name" type="text" class="input" value="{{ old('name', $classRoom->name) }}" required autofocus />
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="code" class="label">Kode (opsional)</label>
                <input id="code" name="code" type="text" class="input" value="{{ old('code', $classRoom->code) }}" />
                @error('code')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="description" class="label">Deskripsi (opsional)</label>
                <textarea id="description" name="description" rows="3" class="input">{{ old('description', $classRoom->description) }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="komting_id" class="label">Komting Pengelola</label>
                <x-ui.searchable-select
                    name="komting_id"
                    placeholder="Pilih komting…"
                    :required="true"
                    :options="$komtings->mapWithKeys(fn ($k) => [$k->id => $k->name.' ('.$k->email.')'])"
                    :selected="[old('komting_id', $classRoom->komting_id)]"
                />
                @error('komting_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button href="{{ route('class-rooms.show', $classRoom) }}" type="outline" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" class="w-full sm:w-auto">Perbarui Kelas</x-ui.button>
            </div>
        </form>

        {{-- Danger zone --}}
        <div class="card p-6 ring-red-100">
            <h3 class="text-sm font-semibold text-red-600">Zona Bahaya</h3>
            <p class="mt-1 text-xs text-gray-500">Menghapus kelas akan menghapus semua mata pelajaran, kelompok, dan tugas di dalamnya.</p>
            <form method="POST" action="{{ route('class-rooms.destroy', $classRoom) }}" data-confirm="Kamu yakin ingin menghapus ruang kelas ini? Tindakan ini tidak dapat dibatalkan." onsubmit="return confirm(this.dataset.confirm)" class="mt-4">
                @csrf
                @method('DELETE')
                <x-ui.button :submit="true" type="danger" class="w-full sm:w-auto">Hapus Kelas</x-ui.button>
            </form>
        </div>
    </div>
</x-app-layout>
