<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Perbarui Informasi Profil</h2>
        <p class="mt-1 text-sm text-gray-500">Perbarui informasi profil dan alamat email akun kamu.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="label">Nama</label>
            <input id="name" name="name" type="text" class="input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" class="input" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')<p class="form-error">{{ $message }}</p>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="text-sm text-gray-600">
                        Alamat email kamu belum terverifikasi.
                        <button form="send-verification" class="font-semibold text-rose-700 hover:text-rose-600">Klik di sini untuk mengirim ulang email verifikasi.</button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-600">Tautan verifikasi baru telah dikirim ke alamat email kamu.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-ui.button :submit="true">Simpan</x-ui.button>
            @if (session('status') === 'profile-updated')
                <span x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-500">Tersimpan.</span>
            @endif
        </div>
    </form>
</section>
