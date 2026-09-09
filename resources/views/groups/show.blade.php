<x-app-layout>
    <x-slot name="title">{{ $group->name }}</x-slot>

    @php($canManage = auth()->user()->can('manage', $group))
    @php($inGroup = in_array(auth()->id(), $members->pluck('id')->all()) || $group->members()->whereKey(auth()->id())->exists())
    @php($isEnrolled = auth()->user()->isStudent() && auth()->user()->subjects->contains($subject->id))

    <x-ui.page-header :title="$group->name" :subtitle="'Kelompok dalam '.$subject->name"
        :back-href="route('class-rooms.subjects.groups.index', [$classRoom, $subject])" back-label="Kelompok">
        @if ($canManage)
            <x-ui.button href="{{ route('class-rooms.subjects.groups.edit', [$classRoom, $subject, $group]) }}" type="secondary" size="sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                Ubah
            </x-ui.button>
        @endif
    </x-ui.page-header>

    <div class="mx-auto max-w-2xl">
        <x-ui.card :title="'Anggota ('.$members->total().')'">
            <x-slot name="action">
                <x-ui.per-page :paginator="$members" />
            </x-slot>

            @if ($canManage && $availableMembers->isNotEmpty())
                <div class="mb-5 rounded-md border border-gray-100 bg-gray-50/60 p-4">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Tambah Anggota</h4>
                    <form method="POST" action="{{ route('class-rooms.subjects.groups.members.store', [$classRoom, $subject, $group]) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        @csrf
                        <div class="flex-1">
                            <x-ui.searchable-select
                                name="user_ids"
                                placeholder="Pilih anggota…"
                                multiple
                                :options="$availableMembers->mapWithKeys(fn ($m) => [$m->id => $m->name.' ('.$m->email.')'])"
                            />
                            @error('user_ids')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <x-ui.button :submit="true" class="min-h-[44px] shrink-0 w-full sm:w-auto">Tambah yang Dipilih</x-ui.button>
                    </form>
                </div>
            @endif

            <div class="space-y-2.5">
                @forelse ($members as $member)
                    <div class="flex items-center gap-3 border-b border-gray-50 pb-2.5 last:border-0">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-bold text-gray-600">{{ Str::limit($member->name, 2, '') }}</span>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-gray-900">{{ $member->name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $member->email }}</p>
                        </div>
                        @if ($member->id === auth()->id())
                            <span class="badge-green ml-auto">Kamu</span>
                        @elseif ($canManage)
                            <form method="POST" action="{{ route('class-rooms.subjects.groups.members.destroy', [$classRoom, $subject, $group, $member]) }}" class="ml-auto" data-confirm="Keluarkan {{ $member->name }} dari kelompok ini?" onsubmit="return confirm(this.dataset.confirm)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">Keluarkan</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <x-ui.empty-state title="Belum ada anggota dalam kelompok ini" description="Anggota akan muncul di sini setelah mereka bergabung atau ditugaskan." />
                @endforelse
            </div>

            <div class="mt-4 border-t border-gray-100 pt-4">
                {{ $members->links() }}
            </div>

            {{-- Student join/leave --}}
            @if ($isEnrolled && $subject->isSelectMode() && ! $subject->groups_locked)
                <div class="mt-5 border-t border-gray-100 pt-4">
                    @if ($inGroup)
                        <form method="POST" action="{{ route('class-rooms.subjects.groups.leave', [$classRoom, $subject, $group]) }}" data-confirm="Tinggalkan kelompok ini?" onsubmit="return confirm(this.dataset.confirm)">
                            @csrf
                            @method('DELETE')
                            <x-ui.button :submit="true" type="danger" class="w-full">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Tinggalkan Kelompok
                            </x-ui.button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('class-rooms.subjects.groups.join', [$classRoom, $subject, $group]) }}">
                            @csrf
                            <x-ui.button :submit="true" type="success" class="w-full">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                                Bergabung ke Kelompok Ini
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            @elseif ($isEnrolled && $subject->isSelectMode() && $subject->groups_locked)
                <p class="mt-5 rounded-md bg-gray-50 p-4 text-center text-sm text-gray-500">Kelompok terkunci. Kamu tidak dapat lagi bergabung atau keluar.</p>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
