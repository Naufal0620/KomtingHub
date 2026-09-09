<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verifikasi Pengacakan · {{ config('app.name', 'KomtingHub') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-gray-900 antialiased">
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14">
            <div class="mb-8 text-center">
                <span class="inline-flex items-center gap-2 rounded-md bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Verifikasi Publik
                </span>
                <h1 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">Verifikasi Pengacakan</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $subject->name }} · {{ $subject->classRoom->name }}</p>
            </div>

            <div class="mb-6 rounded-lg border p-4 {{ $valid ? 'border-emerald-100 bg-emerald-50' : 'border-red-100 bg-red-50' }}">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-md {{ $valid ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                        @if ($valid)
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </span>
                    <div class="text-sm">
                        <p class="font-semibold {{ $valid ? 'text-emerald-800' : 'text-red-800' }}">
                            {{ $valid ? 'Terverifikasi' : 'Tidak Terverifikasi' }}
                        </p>
                        <p class="mt-0.5 {{ $valid ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ $valid ? 'Hash cocok dengan hasil yang dihitung ulang dari biji (seed) dan algoritma yang tercatat.' : 'Hash tidak cocok dengan hasil yang dihitung ulang.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-6 flex flex-wrap justify-center gap-2">
                @if ($memberSetChanged)
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        Daftar anggota mata pelajaran saat ini berbeda dengan saat pengacakan dilakukan.
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5 rounded-md bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                    Nama anggota disamarkan demi privasi.
                </span>
            </div>

            <div class="card mb-6">
                <div class="card-header"><h2 class="card-title">Detail Pengacakan</h2></div>
                <div class="card-body">
                    <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Algoritma</dt>
                            <dd class="mt-1 font-mono">{{ $run->algorithm }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Versi</dt>
                            <dd class="mt-1 font-mono">{{ $run->version }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kelompok</dt>
                            <dd class="mt-1">{{ $run->group_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Dilakukan pada</dt>
                            <dd class="mt-1">{{ $run->created_at }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Biji (seed)</dt>
                            <dd class="mt-1 break-all font-mono">{{ $run->seed }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Hash Komitmen</dt>
                            <dd class="mt-1 break-all font-mono text-xs">{{ $run->hash }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2 class="card-title">Hasil</h2></div>
                <div class="card-body space-y-3">
                    @foreach ($result as $resultGroup)
                        <div class="rounded-md border border-gray-100 p-4">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $resultGroup['group'] }}</h3>
                            <ul class="mt-2 space-y-1 text-sm text-gray-600">
                                @foreach ($resultGroup['members'] as $member)
                                    <li class="flex items-center gap-2">
                                        <span class="h-6 w-6 shrink-0 rounded-md bg-rose-50 text-center text-xs font-semibold leading-6 text-rose-700">{{ Str::limit($member, 1, '') }}</span>
                                        {{ $member }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </body>
</html>
