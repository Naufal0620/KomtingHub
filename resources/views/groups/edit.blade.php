<x-app-layout>
    <x-slot name="title">Ubah Kelompok</x-slot>

    <x-ui.page-header title="Ubah Kelompok" :subtitle="$group->name"
        :back-href="route('class-rooms.subjects.groups.show', [$classRoom, $subject, $group])" :back-label="$group->name" />

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('class-rooms.subjects.groups.update', [$classRoom, $subject, $group]) }}" class="card mb-6 p-6">
            @csrf
            @method('PUT')

            <label for="name" class="label">Nama Kelompok</label>
            <input id="name" name="name" type="text" class="input" value="{{ old('name', $group->name) }}" required autofocus />
            @error('name')<p class="form-error">{{ $message }}</p>@enderror

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button href="{{ route('class-rooms.subjects.groups.show', [$classRoom, $subject, $group]) }}" type="outline" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" class="w-full sm:w-auto">Perbarui Kelompok</x-ui.button>
            </div>
        </form>

        <div class="card p-6 ring-red-100">
            <h3 class="text-sm font-semibold text-red-600">Zona Berbahaya</h3>
            <p class="mt-1 text-xs text-gray-500">Menghapus kelompok ini menghapus semua penugasan anggotanya.</p>
            <form method="POST" action="{{ route('class-rooms.subjects.groups.destroy', [$classRoom, $subject, $group]) }}" data-confirm="Kamu yakin ingin menghapus kelompok ini?" onsubmit="return confirm(this.dataset.confirm)" class="mt-4">
                @csrf
                @method('DELETE')
                <x-ui.button :submit="true" type="danger" class="w-full sm:w-auto">Hapus Kelompok</x-ui.button>
            </form>
        </div>
    </div>
</x-app-layout>
