<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Perbarui Kata Sandi</h2>
        <p class="mt-1 text-sm text-gray-500">Pastikan akun kamu menggunakan kata sandi yang panjang dan acak agar tetap aman.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="label">Kata Sandi Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" class="input" autocomplete="current-password" />
            @error('current_password', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="update_password_password" class="label">Kata Sandi Baru</label>
            <input id="update_password_password" name="password" type="password" class="input" autocomplete="new-password" />
            @error('password', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="label">Konfirmasi Kata Sandi</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="input" autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-4">
            <x-ui.button :submit="true">Simpan</x-ui.button>
            @if (session('status') === 'password-updated')
                <span x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-500">Tersimpan.</span>
            @endif
        </div>
    </form>
</section>
