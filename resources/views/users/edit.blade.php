<x-app-layout>
    <x-slot name="title">Edit Akun</x-slot>

    <x-ui.page-header title="Edit Akun" :subtitle="$user->name"
        back-href="{{ route('users.index') }}" back-label="Kelola Akun" />

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('users.update', $user) }}" class="card p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="label">Nama</label>
                <input id="name" name="name" type="text" class="input" value="{{ old('name', $user->name) }}" required autofocus />
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" class="input" value="{{ old('email', $user->email) }}" required placeholder="nama@contoh.com" />
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="password" class="label">Kata Sandi Baru (opsional)</label>
                <input id="password" name="password" type="password" class="input" autocomplete="new-password" placeholder="Kosongkan untuk tidak mengubah" />
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4">
                <label for="password_confirmation" class="label">Konfirmasi Kata Sandi Baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="input" autocomplete="new-password" placeholder="••••••••" />
            </div>

            <div class="mt-5">
                <span class="label">Peran</span>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label for="role-komting" class="flex cursor-pointer items-center gap-3 rounded-md border border-gray-200 p-3 transition hover:border-gray-300 has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50">
                        <input id="role-komting" type="radio" name="role" value="komting" class="h-4 w-4 accent-rose-700" @checked(old('role', $user->role) === 'komting') required>
                        <span class="text-sm">
                            <span class="block font-semibold text-gray-900">Komting</span>
                            <span class="block text-xs text-gray-500">Mengelola kelas</span>
                        </span>
                    </label>
                    <label for="role-student" class="flex cursor-pointer items-center gap-3 rounded-md border border-gray-200 p-3 transition hover:border-gray-300 has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50">
                        <input id="role-student" type="radio" name="role" value="student" class="h-4 w-4 accent-rose-700" @checked(old('role', $user->role) === 'student')>
                        <span class="text-sm">
                            <span class="block font-semibold text-gray-900">Mahasiswa</span>
                            <span class="block text-xs text-gray-500">Anggota kelas</span>
                        </span>
                    </label>
                </div>
                @error('role')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button href="{{ route('users.index') }}" type="outline" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" class="w-full sm:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Simpan Perubahan
                </x-ui.button>
            </div>
        </form>
    </div>
</x-app-layout>