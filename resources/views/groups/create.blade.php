<x-app-layout>
    <x-slot name="title">Kelompok Baru</x-slot>

    <x-ui.page-header title="Buat Kelompok" :subtitle="$subject->name"
        :back-href="route('class-rooms.subjects.groups.index', [$classRoom, $subject])" back-label="Kelompok" />

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('class-rooms.subjects.groups.store', [$classRoom, $subject]) }}" class="card p-6">
            @csrf
            <label for="name" class="label">Nama Kelompok</label>
            <input id="name" name="name" type="text" class="input" value="{{ old('name') }}" required autofocus placeholder="mis. Tim Alpha" />
            @error('name')<p class="form-error">{{ $message }}</p>@enderror

            @if ($subject->isSelectMode())
                <p class="mt-4 rounded-md bg-rose-50 p-3 text-xs text-rose-800">Anggota dapat bergabung ke kelompok ini sampai kamu mengunci kelompok untuk mata pelajaran ini.</p>
            @endif

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button href="{{ route('class-rooms.subjects.groups.index', [$classRoom, $subject]) }}" type="outline" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" class="w-full sm:w-auto">Buat Kelompok</x-ui.button>
            </div>
        </form>
    </div>
</x-app-layout>
