<x-guest-layout>
    <div class="mb-6 text-sm text-gray-600">
        Ini adalah area aman dari aplikasi. Konfirmasikan kata sandi kamu sebelum melanjutkan.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" novalidate>
        @csrf

        <div>
            <label for="password" class="label">Kata Sandi</label>
            <input id="password" type="password" name="password" class="input" required autocomplete="current-password" placeholder="••••••••" />
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <x-ui.button :submit="true" class="mt-6 w-full">Konfirmasi</x-ui.button>
    </form>
</x-guest-layout>
