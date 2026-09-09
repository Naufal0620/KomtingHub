<x-guest-layout>
    <div class="mb-6 text-sm text-gray-600">
        Terima kasih sudah mendaftar! Sebelum mulai, bisakah kamu memverifikasi alamat email dengan mengklik tautan yang baru saja kami kirimkan ke email kamu? Jika kamu tidak menerima email tersebut, kami dengan senang hati akan mengirimkan yang baru.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-100">
            Tautan verifikasi baru telah dikirim ke alamat email yang kamu gunakan saat mendaftar.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button :submit="true" class="w-full">Kirim Ulang Email Verifikasi</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button :submit="true" type="outline" class="w-full">Keluar</x-ui.button>
        </form>
    </div>
</x-guest-layout>
