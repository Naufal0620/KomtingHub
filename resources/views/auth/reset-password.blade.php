<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" class="input" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="kamu@contoh.com" />
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="password" class="label">Kata Sandi</label>
            <input id="password" type="password" name="password" class="input" required autocomplete="new-password" placeholder="••••••••" />
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="password_confirmation" class="label">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="input" required autocomplete="new-password" placeholder="••••••••" />
            @error('password_confirmation')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <x-ui.button :submit="true" class="mt-6 w-full">Atur Ulang Kata Sandi</x-ui.button>
    </form>
</x-guest-layout>
