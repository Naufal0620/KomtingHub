<x-guest-layout>
    <div class="mb-6 text-sm text-gray-600">
        Lupa kata sandi? Tidak masalah. Beri tahu kami alamat email kamu dan kami akan mengirimkan tautan reset kata sandi.
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-100">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required autofocus placeholder="kamu@contoh.com" />
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <x-ui.button :submit="true" class="mt-6 w-full">Kirim Tautan Reset Kata Sandi</x-ui.button>
    </form>
</x-guest-layout>
