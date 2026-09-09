<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div>
        <label for="email" class="label">Email</label>
        <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="kamu@contoh.com" />
        @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="mt-4">
        <div class="flex items-center justify-between">
            <label for="password" class="label">Kata Sandi</label>
            @if (Route::has('password.request'))
                <a class="text-xs font-semibold text-rose-700 hover:text-rose-600" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>
        <input id="password" type="password" name="password" class="input" required autocomplete="current-password" placeholder="••••••••" />
        @error('password')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="mt-4 flex items-center justify-between">
        <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-600">
            <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-rose-700 focus:ring-rose-600">
            Ingat saya
        </label>
    </div>

<x-ui.button :submit="true" class="mt-6 w-full">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
        Masuk
    </x-ui.button>
</form>
</x-guest-layout>
