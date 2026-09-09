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
    <body class="flex min-h-full flex-col bg-gray-50 text-gray-900 antialiased">

        @php
            $unreadCount = Auth::user()->unreadNotifications()->count();
            $myClasses = Auth::user()->isStudent() ? Auth::user()->classRooms()->orderBy('name')->get() : collect();
            $primaryClass = $myClasses->first();
            $isHome = request()->routeIs('dashboard');
            $isClasses = request()->routeIs('class-rooms.*');
            $isAlerts = request()->routeIs('notifications.*');
            $isProfile = request()->routeIs('profile.*');
        @endphp

        {{-- ============================================================
             Desktop sidebar (md and up), replaces the top navigation
        ============================================================ --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-gray-200/70 bg-white md:flex">
            {{-- Brand --}}
            <a href="{{ route('dashboard') }}" class="flex h-16 shrink-0 items-center gap-2.5 border-b border-gray-100 px-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-md brand-gradient text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </span>
                <span class="text-base font-extrabold tracking-tight text-gray-900">{{ config('app.name', 'KomtingHub') }}</span>
            </a>

            {{-- Nav links --}}
            <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ $isHome ? 'sidebar-link-active' : '' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                    Beranda
                </a>

                @if (Auth::user()->isKomtingOrAdmin())
                    <a href="{{ route('class-rooms.index') }}" class="sidebar-link {{ $isClasses ? 'sidebar-link-active' : '' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                        Kelas
                    </a>
                @else
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button type="button" @click="open = !open" class="sidebar-link w-full justify-between {{ $isClasses ? 'sidebar-link-active' : '' }}">
                            <span class="flex items-center gap-3">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                                Kelas Saya
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>

                        <div x-show="open" class="mt-1 space-y-0.5">
                            @forelse ($myClasses as $classRoom)
                                <a href="{{ route('class-rooms.show', $classRoom) }}" class="sidebar-link pl-8 {{ request()->is('class-rooms/'.$classRoom->id) ? 'sidebar-link-active' : '' }}">
                                    <span class="truncate">{{ $classRoom->name }}</span>
                                </a>
                            @empty
                                <p class="px-3 py-1 pl-8 text-xs text-gray-400">Belum ada kelas.</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                @if (Auth::user()->isAdmin())
                    <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'sidebar-link-active' : '' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        Kelola Akun
                    </a>
                @endif

                <a href="{{ route('notifications.index') }}" class="sidebar-link {{ $isAlerts ? 'sidebar-link-active' : '' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    Notifikasi
                    @if ($unreadCount > 0)
                        <span class="badge-red ml-auto px-2 py-0.5">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </a>

                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ $isProfile ? 'sidebar-link-active' : '' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    Profil
                </a>
            </nav>

            {{-- Current user + logout --}}
            <div class="shrink-0 border-t border-gray-100 p-3">
                <div class="flex items-center gap-3 rounded-md px-2 py-2">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md brand-gradient text-xs font-bold text-white">
                        {{ Str::limit(Auth::user()->name, 2, '') }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="truncate text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Keluar" aria-label="Keluar">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ============================================================
             Mobile top bar (below md only)
        ============================================================ --}}
        <header class="sticky top-0 z-30 border-b border-gray-200/70 bg-white/80 backdrop-blur-md md:hidden">
            <div class="flex h-14 items-center justify-between px-4 sm:px-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-md brand-gradient text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </span>
                    <span class="text-base font-extrabold tracking-tight text-gray-900">{{ config('app.name', 'KomtingHub') }}</span>
                </a>

                <div class="flex items-center gap-1.5">
                    {{-- Notifications bell --}}
                    <a href="{{ route('notifications.index') }}"
                       class="relative flex h-10 w-10 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
                       aria-label="Notifikasi">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        @if ($unreadCount > 0)
                            <span class="absolute right-1 top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>

                    {{-- Profile dropdown --}}
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-md p-1.5 pl-2 transition hover:bg-gray-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-md brand-gradient text-xs font-bold text-white">
                                {{ Str::limit(Auth::user()->name, 2, '') }}
                            </span>
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>

                        <div x-show="open" x-transition
                             class="absolute right-0 mt-2 w-52 overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-gray-900/5">
                            <div class="border-b border-gray-100 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                Profil
                            </a>
                            @if (Auth::user()->isAdmin())
                                <a href="{{ route('users.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                    Kelola Akun
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ============================================================
             Page content
        ============================================================ --}}
        <main class="flex-1 pb-20 pt-5 sm:pt-8 md:pb-10 md:pl-72 md:pt-8">
            <div class="app-container">
                @if (session('success'))
                    <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
                @endif
                @if (session('error'))
                    <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
                @endif

                {{ $slot }}
            </div>
        </main>

        {{-- ============================================================
             Mobile bottom navigation (below md only)
        ============================================================ --}}
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 backdrop-blur-md md:hidden safe-bottom">
            <div class="grid grid-cols-4">
                <a href="{{ route('dashboard') }}" aria-current="{{ $isHome ? 'page' : '' }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold transition {{ $isHome ? 'text-rose-700' : 'text-gray-500' }}">
                    <span class="flex h-8 w-16 items-center justify-center rounded-lg {{ $isHome ? 'bg-rose-50' : '' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                    </span>
                    Beranda
                </a>

                @if (Auth::user()->isKomtingOrAdmin())
                    <a href="{{ route('class-rooms.index') }}" aria-current="{{ $isClasses ? 'page' : '' }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold transition {{ $isClasses ? 'text-rose-700' : 'text-gray-500' }}">
                        <span class="flex h-8 w-16 items-center justify-center rounded-lg {{ $isClasses ? 'bg-rose-50' : '' }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                        </span>
                        Kelas
                    </a>
                @else
                    <a href="{{ $primaryClass ? route('class-rooms.show', $primaryClass) : route('dashboard') }}" aria-current="{{ $isClasses ? 'page' : '' }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold transition {{ $isClasses ? 'text-rose-700' : 'text-gray-500' }}">
                        <span class="flex h-8 w-16 items-center justify-center rounded-lg {{ $isClasses ? 'bg-rose-50' : '' }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                        </span>
                        Kelas Saya
                    </a>
                @endif

                <a href="{{ route('notifications.index') }}" aria-current="{{ $isAlerts ? 'page' : '' }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold transition {{ $isAlerts ? 'text-rose-700' : 'text-gray-500' }}">
                    <span class="relative flex h-8 w-16 items-center justify-center rounded-lg {{ $isAlerts ? 'bg-rose-50' : '' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        @if ($unreadCount > 0)
                            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[9px] font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </span>
                    Notifikasi
                </a>

                <a href="{{ route('profile.edit') }}" aria-current="{{ $isProfile ? 'page' : '' }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold transition {{ $isProfile ? 'text-rose-700' : 'text-gray-500' }}">
                    <span class="flex h-8 w-16 items-center justify-center rounded-lg {{ $isProfile ? 'bg-rose-50' : '' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    Profil
                </a>
            </div>
        </nav>
    </body>
</html>