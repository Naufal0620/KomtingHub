<x-app-layout>
    <x-slot name="title">Ubah Mata Pelajaran</x-slot>

    <x-ui.page-header title="Ubah Mata Pelajaran" :subtitle="$subject->name"
        :back-href="route('class-rooms.subjects.show', [$classRoom, $subject])" :back-label="$subject->name" />

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('class-rooms.subjects.update', [$classRoom, $subject]) }}" class="card mb-6 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="label">Nama</label>
                <input id="name" name="name" type="text" class="input" value="{{ old('name', $subject->name) }}" required autofocus />
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="code" class="label">Kode (opsional)</label>
                <input id="code" name="code" type="text" class="input" value="{{ old('code', $subject->code) }}" />
                @error('code')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="description" class="label">Deskripsi (opsional)</label>
                <textarea id="description" name="description" rows="3" class="input">{{ old('description', $subject->description) }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label class="label">Mode Pembentukan Kelompok</label>
                <div class="grid grid-cols-2 gap-3">
                    @php($mode = old('group_mode', $subject->group_mode))
                    <label class="cursor-pointer rounded-md border p-4 transition focus-within:ring-2 focus-within:ring-rose-600 focus-within:ring-offset-2 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-600">
                        <input type="radio" name="group_mode" value="random" class="sr-only" @checked($mode === 'random')>
                        <span class="flex items-center gap-2 font-semibold text-gray-900">
                            <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            Acak Otomatis
                        </span>
                        <span class="mt-1 block text-xs text-gray-500">Komting menjalankan pengacakan yang transparan dan dapat diaudit.</span>
                    </label>

                    <label class="cursor-pointer rounded-md border p-4 transition focus-within:ring-2 focus-within:ring-rose-600 focus-within:ring-offset-2 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-600">
                        <input type="radio" name="group_mode" value="select" class="sr-only" @checked($mode === 'select')>
                        <span class="flex items-center gap-2 font-semibold text-gray-900">
                            <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                            Pilih Sendiri
                        </span>
                        <span class="mt-1 block text-xs text-gray-500">Mahasiswa memilih kelompoknya sendiri hingga dikunci.</span>
                    </label>
                </div>
                @error('group_mode')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button href="{{ route('class-rooms.subjects.show', [$classRoom, $subject]) }}" type="outline" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" class="w-full sm:w-auto">Perbarui Mata Pelajaran</x-ui.button>
            </div>
        </form>

        <div class="card p-6 ring-red-100">
            <h3 class="text-sm font-semibold text-red-600">Zona Bahaya</h3>
            <p class="mt-1 text-xs text-gray-500">Menghapus mata pelajaran ini akan menghapus kelompok dan tugasnya.</p>
            <form method="POST" action="{{ route('class-rooms.subjects.destroy', [$classRoom, $subject]) }}" data-confirm="Kamu yakin ingin menghapus mata pelajaran ini?" onsubmit="return confirm(this.dataset.confirm)" class="mt-4">
                @csrf
                @method('DELETE')
                <x-ui.button :submit="true" type="danger" class="w-full sm:w-auto">Hapus Mata Pelajaran</x-ui.button>
            </form>
        </div>
    </div>
</x-app-layout>
