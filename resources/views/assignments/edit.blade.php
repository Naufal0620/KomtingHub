<x-app-layout>
    <x-slot name="title">Ubah Tugas</x-slot>

    <x-ui.page-header title="Ubah Tugas" :subtitle="$assignment->title"
        :back-href="route('class-rooms.subjects.assignments.show', [$classRoom, $subject, $assignment])" :back-label="$assignment->title" />

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('class-rooms.subjects.assignments.update', [$classRoom, $subject, $assignment]) }}" class="card mb-6 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="label">Judul</label>
                <input id="title" name="title" type="text" class="input" value="{{ old('title', $assignment->title) }}" required autofocus />
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="description" class="label">Deskripsi (opsional)</label>
                <textarea id="description" name="description" rows="4" class="input">{{ old('description', $assignment->description) }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="due_date" class="label">Tanggal Tenggat (opsional)</label>
                <input id="due_date" name="due_date" type="datetime-local" class="input" value="{{ old('due_date', $assignment->due_date?->format('Y-m-d\TH:i')) }}" />
                @error('due_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label class="label">Kategori</label>
                <div class="grid grid-cols-2 gap-3">
                    @php($type = old('type', $assignment->type))
                    <label class="cursor-pointer rounded-md border p-4 transition focus-within:ring-2 focus-within:ring-rose-600 focus-within:ring-offset-2 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-600">
                        <input type="radio" name="type" value="individual" class="sr-only" @checked($type === 'individual')>
                        <span class="font-semibold text-gray-900">Individu</span>
                        <span class="mt-1 block text-xs text-gray-500">Setiap anggota dilacak &amp; dinilai secara terpisah.</span>
                    </label>
                    <label class="cursor-pointer rounded-md border p-4 transition focus-within:ring-2 focus-within:ring-rose-600 focus-within:ring-offset-2 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-600">
                        <input type="radio" name="type" value="group" class="sr-only" @checked($type === 'group')>
                        <span class="font-semibold text-gray-900">Kelompok</span>
                        <span class="mt-1 block text-xs text-gray-500">Dikerjakan sebagai upaya tim.</span>
                    </label>
                </div>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label class="label">Metode Pengumpulan</label>
                <div class="grid grid-cols-2 gap-3">
                    @php($mode = old('submission_mode', $assignment->submission_mode))
                    <label class="cursor-pointer rounded-md border p-4 transition focus-within:ring-2 focus-within:ring-rose-600 focus-within:ring-offset-2 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-600">
                        <input type="radio" name="submission_mode" value="none" class="sr-only" @checked($mode === 'none')>
                        <span class="font-semibold text-gray-900">Tidak perlu berkas</span>
                        <span class="mt-1 block text-xs text-gray-500">Mahasiswa cukup menandai selesai.</span>
                    </label>
                    <label class="cursor-pointer rounded-md border p-4 transition focus-within:ring-2 focus-within:ring-rose-600 focus-within:ring-offset-2 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-600">
                        <input type="radio" name="submission_mode" value="file" class="sr-only" @checked($mode === 'file')>
                        <span class="font-semibold text-gray-900">Unggah berkas</span>
                        <span class="mt-1 block text-xs text-gray-500">Mahasiswa mengunggah satu atau beberapa berkas.</span>
                    </label>
                    <label class="cursor-pointer rounded-md border p-4 transition focus-within:ring-2 focus-within:ring-rose-600 focus-within:ring-offset-2 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-600">
                        <input type="radio" name="submission_mode" value="link" class="sr-only" @checked($mode === 'link')>
                        <span class="font-semibold text-gray-900">Kirim tautan</span>
                        <span class="mt-1 block text-xs text-gray-500">Mahasiswa mengirimkan tautan pekerjaan mereka.</span>
                    </label>
                    <label class="cursor-pointer rounded-md border p-4 transition focus-within:ring-2 focus-within:ring-rose-600 focus-within:ring-offset-2 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-600">
                        <input type="radio" name="submission_mode" value="file_link" class="sr-only" @checked($mode === 'file_link')>
                        <span class="font-semibold text-gray-900">Berkas &amp; tautan</span>
                        <span class="mt-1 block text-xs text-gray-500">Mahasiswa mengunggah berkas sekaligus tautan.</span>
                    </label>
                </div>
                @error('submission_mode')<p class="form-error">{{ $message }}</p>@enderror

                <div id="submission-options" class="mt-4 rounded-md border border-gray-200 bg-gray-50 p-4">
                    @php($exts = (array) old('allowed_extensions', $assignment->allowed_extensions ? explode(',', $assignment->allowed_extensions) : []))
                    <div>
                        <label for="max_files" class="label">Maksimum Jumlah Berkas</label>
                        <input id="max_files" name="max_files" type="number" min="1" max="20" class="input" value="{{ old('max_files', $assignment->max_files) }}" />
                        <p class="mt-1 text-xs text-gray-500">Berapa banyak berkas yang boleh diunggah oleh mahasiswa?</p>
                        @error('max_files')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="label">Ekstensi yang Diperbolehkan</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach (array_merge(\App\Models\Assignment::defaultExtensionList(), ['txt', 'zip', 'jpg', 'jpeg', 'png']) as $ext)
                                <label class="flex cursor-pointer items-center gap-1.5 rounded-md border bg-white px-2.5 py-1.5 text-xs text-gray-700 focus-within:ring-2 focus-within:ring-rose-600 has-[:checked]:border-rose-700 has-[:checked]:bg-rose-50 has-[:checked]:text-rose-800">
                                    <input type="checkbox" name="allowed_extensions[]" value="{{ $ext }}" class="sr-only" @checked(in_array($ext, $exts))>
                                    .{{ $ext }}
                                </label>
                            @endforeach
                        </div>
                        @error('allowed_extensions')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4">
                        <label for="max_file_size_kb" class="label">Ukuran Maksimum Per Berkas (KB)</label>
                        <input id="max_file_size_kb" name="max_file_size_kb" type="number" min="100" max="102400" class="input" value="{{ old('max_file_size_kb', $assignment->max_file_size_kb) }}" />
                        <p class="mt-1 text-xs text-gray-500">Contoh: 10240 KB = 10 MB per berkas.</p>
                        @error('max_file_size_kb')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button href="{{ route('class-rooms.subjects.assignments.show', [$classRoom, $subject, $assignment]) }}" type="outline" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" class="w-full sm:w-auto">Perbarui Tugas</x-ui.button>
            </div>
        </form>

        <div class="card p-6 ring-red-100">
            <h3 class="text-sm font-semibold text-red-600">Zona Berbahaya</h3>
            <p class="mt-1 text-xs text-gray-500">Menghapus tugas ini menghapus semua kemajuan anggota.</p>
            <form method="POST" action="{{ route('class-rooms.subjects.assignments.destroy', [$classRoom, $subject, $assignment]) }}" data-confirm="Kamu yakin ingin menghapus tugas ini?" onsubmit="return confirm(this.dataset.confirm)" class="mt-4">
                @csrf
                @method('DELETE')
                <x-ui.button :submit="true" type="danger" class="w-full sm:w-auto">Hapus Tugas</x-ui.button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const options = document.getElementById('submission-options');
            const radios = document.querySelectorAll('input[name="submission_mode"]');
            if (!options || !radios.length) return;
            const toggle = () => {
                const checked = document.querySelector('input[name="submission_mode"]:checked');
                options.classList.toggle('hidden', checked?.value !== 'file' && checked?.value !== 'file_link');
            };
            radios.forEach((radio) => radio.addEventListener('change', toggle));
            toggle();
        })();
    </script>
</x-app-layout>
