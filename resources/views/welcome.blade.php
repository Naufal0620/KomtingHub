<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'KomtingHub') }} | Kelola kelas, sesuai keinginanmu</title>
        <meta name="description" content="KomtingHub membantu perwakilan kelas (komting) mengelola kelas, mata kuliah, kelompok dan tugas, dengan pembentukan kelompok acak yang transparan dan dapat diaudit.">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-full flex-col bg-gray-50">

        {{-- Nav --}}
        <header class="sticky top-0 z-30 border-b border-gray-200/60 bg-white/80 backdrop-blur-md">
            <div class="app-container flex h-16 items-center justify-between">
                <a href="/" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-md brand-gradient text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </span>
                    <span class="text-lg font-extrabold tracking-tight text-gray-900">{{ config('app.name', 'KomtingHub') }}</span>
                </a>

                <nav class="flex items-center gap-2">
                    <x-ui.button href="{{ route('login') }}" size="sm" class="sm:px-5">Masuk</x-ui.button>
                </nav>
            </div>
        </header>

        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-32 top-0 h-96 w-96 rounded-full brand-gradient opacity-20 blur-3xl"></div>
                <div class="absolute -right-24 top-40 h-80 w-80 rounded-full bg-rose-400 opacity-20 blur-3xl"></div>
            </div>

            <div class="app-container relative flex flex-col items-center py-16 text-center sm:py-24">
                <span class="badge-rose mb-5">Dibuat untuk perwakilan kelas</span>
                <h1 class="max-w-3xl text-4xl font-extrabold leading-tight tracking-tight text-gray-900 sm:text-6xl">
                    Kelola <span class="brand-gradient bg-clip-text text-transparent">kelas</span>, kelompok &amp; tugas kamu
                </h1>
                <p class="mt-5 max-w-2xl text-base text-gray-600 sm:text-lg">
                    KomtingHub memberikan komting semua yang dibutuhkan: kelas, mata kuliah, kelompok dan tugas,
                    dengan pembentukan kelompok acak yang adil, <strong>transparan dan dapat diaudit</strong> untuk setiap mata kuliah.
                </p>

                <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row">
                    <x-ui.button href="{{ route('login') }}" class="!px-6 !py-3 text-base">
                        Masuk ke platform
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </x-ui.button>
                    <x-ui.button href="#cara-kerja" type="outline" class="!px-6 !py-3 text-base">Lihat cara kerja</x-ui.button>
                </div>
                <p class="mt-6 text-sm text-gray-500">Akun komting dan mahasiswa dibuat serta dikelola oleh admin.</p>
            </div>
        </section>

        {{-- Features --}}
        <section class="border-t border-gray-100 bg-white py-16 sm:py-20">
            <div class="app-container">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Semua yang dibutuhkan komting</h2>
                    <p class="mt-3 text-gray-600">Dari spesialisasi hingga pengumpulan, kelola semuanya dalam satu tempat.</p>
                </div>

                <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $features = [
                            ['Kelas & Mata Kuliah', 'Mengatur siswa ke dalam kelas dan mendaftarkan mereka ke berbagai mata kuliah.', 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
                            ['Pengacakan transparan', 'Mengacak dan menetapkan kelompok dengan seed yang tercatat dan hash SHA-256 yang dapat diverifikasi. Siapa pun bisa memeriksa hasilnya.', 'M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88'],
                            ['Mode pemilihan mandiri', 'Biarkan siswa bebas masuk atau keluar dari kelompok sebelum kamu menguncinya secara permanen.', 'M4.5 4.5l15 15m0 0V8.25m0 0H8.25'],
                            ['Tugas & penilaian', 'Buat tugas per mata kuliah dan lacak setiap anggota dari status tertunda hingga dinilai.', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                            ['Ekspor cepat', 'Unduh hasil kelompok dan rekapitulasi tugas dalam format Excel.', 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3'],
                            ['Notifikasi cerdas', 'Siswa mendapatkan notifikasi dalam aplikasi dan email saat kelompok terkunci atau tugas baru diterbitkan.', 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'],
                        ];
                    @endphp

                    @foreach ($features as [$ftitle, $fdesc, $ficon])
                        <div class="card p-6 transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-md brand-gradient text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ficon }}"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900">{{ $ftitle }}</h3>
                            <p class="mt-1.5 text-sm text-gray-600">{{ $fdesc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section id="cara-kerja" class="scroll-mt-20 py-16 sm:py-20">
            <div class="app-container">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Cara kerja</h2>
                    <p class="mt-3 text-gray-600">Tiga langkah mudah untuk kelas yang terorganisir.</p>
                </div>

                <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-3">
                    @php
                        $steps = [
                            ['Siapkan kelas kamu', 'Buat kelas, tambahkan mata kuliah dan daftarkan siswa kamu.'],
                            ['Bentuk kelompok sesuai keinginanmu', 'Pilih pemilihan mandiri atau pengacakan transparan, lalu kunci.'],
                            ['Lacak tugas', 'Buat tugas, tinjau pengumpulan dan beri nilai setiap anggota.'],
                        ];
                    @endphp
                    @foreach ($steps as $i => [$stitle, $sdesc])
                        <div class="card relative p-6 transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-md brand-gradient text-sm font-bold text-white">{{ $i + 1 }}</div>
                            <h3 class="font-bold text-gray-900">{{ $stitle }}</h3>
                            <p class="mt-1.5 text-sm text-gray-600">{{ $sdesc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="app-container pb-16 sm:pb-24">
            <div class="brand-gradient relative overflow-hidden rounded-lg px-6 py-12 text-center text-white sm:px-12 sm:py-16">
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-16 -left-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
                <h2 class="relative text-2xl font-extrabold tracking-tight sm:text-4xl">Sudah punya akun?</h2>
                <p class="relative mx-auto mt-3 max-w-xl text-white/85">Masuk untuk melanjutkan mengelola kelas, kelompok dan tugas kamu.</p>
                <a href="{{ route('login') }}" class="relative mt-7 inline-flex items-center gap-2 rounded-md bg-white px-6 py-3 text-base font-bold text-rose-800 shadow-lg transition hover:-translate-y-0.5">
                    Masuk ke KomtingHub
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="border-t border-gray-200 bg-white">
            <div class="app-container flex flex-col items-center justify-between gap-3 py-6 text-sm text-gray-500 sm:flex-row">
                <span class="font-semibold text-gray-700">{{ config('app.name', 'KomtingHub') }}</span>
                <span>© {{ date('Y') }} · Kelola kelas, sesuai keinginanmu.</span>
            </div>
        </footer>
    </body>
</html>