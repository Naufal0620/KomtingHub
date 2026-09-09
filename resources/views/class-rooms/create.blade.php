<x-app-layout>
    <x-slot name="title">Kelas Baru</x-slot>

    <x-ui.page-header title="Buat Ruang Kelas" subtitle="Buat kelas lalu tugaskan kepada komting yang mengelolanya."
        back-href="{{ route('class-rooms.index') }}" back-label="Ruang Kelas" />

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('class-rooms.store') }}" class="card p-6">
            @csrf

            <div>
                <label for="name" class="label">Nama</label>
                <input id="name" name="name" type="text" class="input" value="{{ old('name') }}" required autofocus />
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="code" class="label">Kode (opsional)</label>
                <input id="code" name="code" type="text" class="input" value="{{ old('code') }}" placeholder="e.g. IF-2024" />
                @error('code')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="description" class="label">Deskripsi (opsional)</label>
                <textarea id="description" name="description" rows="3" class="input">{{ old('description') }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="komting_id" class="label">Komting Pengelola</label>
                <x-ui.searchable-select
                    name="komting_id"
                    placeholder="Pilih komting…"
                    :required="true"
                    :options="$komtings->mapWithKeys(fn ($k) => [$k->id => $k->name.' ('.$k->email.')'])"
                    :selected="old('komting_id') ? [old('komting_id')] : []"
                />
                @error('komting_id')<p class="form-error">{{ $message }}</p>@enderror
                @if ($komtings->isEmpty())
                    <p class="mt-1 text-xs text-gray-500">Belum ada akun dengan peran komting. Buat akun komting terlebih dahulu agar dapat ditugaskan.</p>
                @endif
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button href="{{ route('class-rooms.index') }}" type="outline" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" class="w-full sm:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Buat Kelas
                </x-ui.button>
            </div>
        </form>
    </div>
</x-app-layout>
