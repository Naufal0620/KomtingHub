<x-app-layout>
    <x-slot name="title">Acak Ulang Kelompok</x-slot>

    <x-ui.page-header title="Acak Ulang Kelompok" :subtitle="$subject->name"
        :back-href="route('class-rooms.subjects.groups.index', [$classRoom, $subject])" back-label="Kelompok" />

    <div class="mx-auto max-w-xl">
        @if ($subject->groups_locked)
            <x-ui.alert type="warning" class="mb-6">
                Kelompok saat ini terkunci. Buka kunci sebelum mengacak ulang.
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('class-rooms.subjects.shuffle.store', [$classRoom, $subject]) }}" class="card p-6">
            @csrf

            <div>
                <label for="group_count" class="label">Jumlah Kelompok</label>
                <input id="group_count" name="group_count" type="number" min="1" class="input" value="{{ old('group_count') }}" />
                <p class="mt-1 text-xs text-gray-500">Berikan salah satu: jumlah kelompok ATAU anggota per kelompok, bukan keduanya.</p>
                @error('group_count')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="members_per_group" class="label">Anggota per Kelompok</label>
                <input id="members_per_group" name="members_per_group" type="number" min="1" class="input" value="{{ old('members_per_group') }}" />
                @error('members_per_group')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="seed" class="label">Biji (seed) (opsional, untuk reproduksibilitas)</label>
                <input id="seed" name="seed" type="text" class="input" value="{{ old('seed') }}" placeholder="mis. ujian-2026-putaran-1" />
                @error('seed')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button href="{{ route('class-rooms.subjects.groups.index', [$classRoom, $subject]) }}" type="outline" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" class="w-full sm:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a.75.75 0 11-.75.75.75.75 0 01.75-.75zM12 8.25A.75.75 0 1111.25 9 .75.75 0 0112 8.25zm-4.5-3A.75.75 0 116.75 6 4.5 4.5 0 018.25 5.25zm9 1.5a.75.75 0 10.75-.75.75.75 0 00-.75.75zM17.25 8.25a.75.75 0 11-.75.75.75.75 0 01.75-.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.5h14.266c.621 0 1.125-.504 1.125-1.125V10.03l-5.376-3.68A1.125 1.125 0 0013.7 6H6.3a1.125 1.125 0 00-.768.35l-5.09 5.071v8.954c0 .621.504 1.125 1.125 1.125z"/></svg>
                    Acak Ulang
                </x-ui.button>
            </div>
        </form>
    </div>
</x-app-layout>
