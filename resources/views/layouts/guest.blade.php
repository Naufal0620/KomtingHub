<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · '.config('app.name', 'KomtingHub') : config('app.name', 'KomtingHub') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-full flex-col bg-gray-50">
        <div class="relative flex min-h-full flex-1 flex-col items-center justify-center px-4 py-10 sm:py-16">
            {{-- Decorative background --}}
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full brand-gradient opacity-20 blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-rose-400 opacity-20 blur-3xl"></div>
            </div>

            <div class="relative w-full max-w-sm">
                {{-- Brand --}}
                <a href="/" class="mb-6 flex flex-col items-center gap-2">
                    <span class="flex h-14 w-14 items-center justify-center rounded-lg brand-gradient shadow-lg shadow-rose-900/25">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </span>
                    <span class="text-xl font-extrabold tracking-tight text-gray-900">{{ config('app.name', 'KomtingHub') }}</span>
                    <span class="text-xs font-medium text-gray-500">Kelola kelas, sesuai keinginanmu.</span>
                </a>

                {{-- Card --}}
                <div class="rounded-xl bg-white p-6 shadow-xl shadow-gray-900/5 ring-1 ring-gray-900/5 sm:p-8">
                    @if (isset($slot))
                        {{ $slot }}
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>
