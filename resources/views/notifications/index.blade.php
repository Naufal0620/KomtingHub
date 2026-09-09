<x-app-layout>
    <x-slot name="title">Notifikasi</x-slot>

    @php($unread = auth()->user()->unreadNotifications->count())

    <x-ui.page-header title="Notifikasi" back-href="{{ route('dashboard') }}" back-label="Dashboard">
        @if ($notifications->total() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <x-ui.button :submit="true" type="outline" size="sm">Tandai semua sebagai sudah dibaca</x-ui.button>
            </form>
            <form method="POST" action="{{ route('notifications.clear-all') }}" onsubmit="return confirm('Hapus semua notifikasi?')">
                @csrf
                @method('DELETE')
                <x-ui.button :submit="true" type="danger" size="sm">Hapus semua</x-ui.button>
            </form>
        @endif
    </x-ui.page-header>

    <div class="mx-auto mb-4 flex max-w-2xl items-center justify-between gap-3">
        <p class="text-sm text-gray-500">{{ $notifications->total() }} notifikasi{{ $unread > 0 ? " • {$unread} belum dibaca" : '' }}</p>
        <x-ui.per-page :paginator="$notifications" />
    </div>

    <div class="mx-auto max-w-2xl space-y-3">
        @forelse ($notifications as $notification)
            @php($url = $notification->data['url'] ?? null)
            <div class="flex items-start justify-between gap-3 rounded-lg border p-4 {{ $notification->read_at ? 'border-gray-100 bg-white' : 'border-rose-100 bg-rose-50/60' }}">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-md {{ $notification->read_at ? 'bg-gray-100 text-gray-400' : 'bg-rose-100 text-rose-700' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    </span>
                    <div class="min-w-0">
                        @if ($url)
                            <a href="{{ $url }}" class="block text-sm underline-offset-2 hover:underline {{ $notification->read_at ? 'text-gray-600' : 'font-medium text-gray-900' }}">
                                {{ $notification->data['message'] ?? 'Notifikasi' }}
                            </a>
                        @else
                            <p class="text-sm {{ $notification->read_at ? 'text-gray-600' : 'font-medium text-gray-900' }}">
                                {{ $notification->data['message'] ?? 'Notifikasi' }}
                            </p>
                        @endif
                        <p class="mt-0.5 text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-rose-700 hover:text-rose-600">Baca</button>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-red-600">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Kamu sudah beres" description="Saat ini kamu tidak memiliki notifikasi." />
        @endforelse

        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>