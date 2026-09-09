<x-app-layout>
    <x-slot name="title">Hasil Pengacakan</x-slot>

    <x-ui.page-header title="Hasil Pengacakan" :subtitle="$subject->name"
        :back-href="route('class-rooms.subjects.groups.index', [$classRoom, $subject])" back-label="Kelompok">
    </x-ui.page-header>

    {{-- Run details --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Detail Pengacakan</h3>
        </div>
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
                <div class="col-span-2 sm:col-span-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Biji (seed)</dt>
                    <dd class="mt-1 font-mono break-all">{{ $run->seed }}</dd>
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Hash Komitmen</dt>
                    <dd class="mt-1 break-all font-mono text-xs">{{ $run->hash }}</dd>
                </div>
            </dl>

            <div class="mt-5">
                <x-ui.button :href="route('shuffle.verify', $run)" target="_blank" type="secondary" size="sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    Buka Halaman Verifikasi
                </x-ui.button>
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil</h3>
        </div>
        <div class="card-body space-y-3">
            @foreach ($run->result as $result)
                <div class="rounded-md border border-gray-100 p-4">
                    <h4 class="text-sm font-semibold text-gray-900">{{ $result['group'] }}</h4>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        @foreach ($result['members'] as $memberId)
                            @php($memberName = $nameMap[(int) $memberId] ?? 'Pengguna #'.$memberId)
                            <li class="flex items-center gap-2">
                                <span class="h-6 w-6 shrink-0 rounded-md bg-rose-50 text-center text-xs font-semibold leading-6 text-rose-700">
                                    {{ Str::limit($memberName, 1, '') }}
                                </span>
                                {{ $memberName }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
