<x-app-layout>
    <x-slot name="title">{{ $assignment->title }}</x-slot>

    @php($canManage = auth()->user()->can('manage', $subject))

    <x-ui.page-header :title="$assignment->title" :subtitle="'Tugas untuk '.$subject->name"
        :back-href="route('class-rooms.subjects.assignments.index', [$classRoom, $subject])" back-label="Tugas">
        @if ($canManage)
            <x-ui.button href="{{ route('class-rooms.subjects.assignments.edit', [$classRoom, $subject, $assignment]) }}" type="secondary" size="sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                Ubah
            </x-ui.button>
        @endif
    </x-ui.page-header>

    {{-- Details card --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge-rose">{{ $assignment->type === 'group' ? 'Kelompok' : 'Individu' }}</span>
                @if ($assignment->requiresFile() && $assignment->requiresLink())
                    <span class="badge-blue">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                        Berkas &amp; tautan
                    </span>
                @elseif ($assignment->requiresFile())
                    <span class="badge-blue">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                        Pengumpulan berkas
                    </span>
                @elseif ($assignment->requiresLink())
                    <span class="badge-blue">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                        Pengumpulan tautan
                    </span>
                @else
                    <span class="badge-gray">Tanpa unggahan berkas</span>
                @endif
                @if ($assignment->requiresFile())
                    <span class="badge-gray">Maks {{ $assignment->max_files }} berkas
                        @if ($assignment->allowedExtensionList())
                            · format .{{ implode(', .', $assignment->allowedExtensionList()) }}
                        @endif
                        · {{ number_format($assignment->max_file_size_kb / 1024, 1) }} MB/berkas</span>
                @endif
                @if ($assignment->due_date)
                    <span class="badge-gray">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        Tenggat {{ $assignment->due_date->format('M j, Y H:i') }}
                    </span>
                @endif
            </div>
            @if ($assignment->description)
                <p class="mt-4 whitespace-pre-line text-sm text-gray-600">{{ $assignment->description }}</p>
            @endif
        </div>
    </div>

    @if ($canManage)
        {{-- Progress / grading table --}}
        <x-ui.card :title="'Pelacakan Kemajuan ('.$assignmentUsers->total().')'">
            <x-slot name="action">
                <x-ui.per-page :paginator="$assignmentUsers" />
            </x-slot>
            <div class="-mx-4 hidden overflow-x-auto px-4 md:block sm:mx-0 sm:px-0">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="py-2.5 pr-3">Mahasiswa</th>
                            <th class="py-2.5 pr-3">Status</th>
                            <th class="py-2.5 pr-3">Dikumpulkan</th>
                            <th class="py-2.5 pr-3">Tautan</th>
                            <th class="py-2.5 pr-3">Berkas</th>
                            <th class="py-2.5 pr-3">Nilai</th>
                            <th class="py-2.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignmentUsers as $user)
                            @php($userFiles = $assignment->submissions->where('user_id', $user->id))
                            <tr class="border-b border-gray-50 last:border-0">
                                <td class="py-2.5 pr-3 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="py-2.5 pr-3">
                                    <span class="badge-{{ $user->pivot->status === 'graded' ? 'green' : ($user->pivot->status === 'done' ? 'rose-deep' : 'amber') }}">{{ match ($user->pivot->status) { 'graded' => 'Dinilai', 'done' => 'Selesai', default => 'Tertunda' } }}</span>
                                </td>
                                <td class="py-2.5 pr-3 text-gray-500">{{ $user->pivot->submitted_at?->format('M j, H:i') ?? 'Belum' }}</td>
                                <td class="py-2.5 pr-3 text-xs">
                                    @if ($user->pivot->submission_url)
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ $user->pivot->submission_url }}" target="_blank" rel="noopener" class="max-w-[160px] truncate text-rose-700 hover:underline" title="{{ $user->pivot->submission_url }}">{{ $user->pivot->submission_url }}</a>
                                            <form method="POST" action="{{ route('class-rooms.subjects.assignments.submissions.link.destroy', [$classRoom, $subject, $assignment, $user]) }}" data-confirm="Hapus tautan ini?" onsubmit="return confirm(this.dataset.confirm)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-3">
                                    @forelse ($userFiles as $file)
                                        <div class="flex items-center gap-1.5 py-0.5 text-xs">
                                            <a href="{{ route('class-rooms.subjects.assignments.submissions.download', [$classRoom, $subject, $assignment, $file]) }}" class="max-w-[160px] truncate text-rose-700 hover:underline" title="{{ $file->original_name }}">{{ $file->original_name }}</a>
                                            @if ($file->is_late)
                                                <span class="badge-amber">Telat</span>
                                            @endif
<form method="POST" action="{{ route('class-rooms.subjects.assignments.submissions.destroy', [$classRoom, $subject, $assignment, $file]) }}" data-confirm="Hapus berkas ini?" onsubmit="return confirm(this.dataset.confirm)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">Hapus</button>
                                            </form>
                                        </div>
                                    @empty
                                        <span class="text-gray-400">-</span>
                                    @endforelse
                                </td>
                                <td class="py-2.5 pr-3 font-semibold text-gray-900">{{ $user->pivot->grade ?? '-' }}</td>
                                <td class="py-2.5">
                                    <form method="POST" action="{{ route('class-rooms.subjects.assignments.grade', [$classRoom, $subject, $assignment, $user]) }}" class="flex items-center gap-1.5">
                                        @csrf
                                        <input type="number" name="grade" min="0" max="100" step="0.01" value="{{ $user->pivot->grade }}" class="input w-20 py-1.5" placeholder="Nilai" />
                                        <input type="text" name="feedback" value="{{ $user->pivot->feedback }}" class="input w-24 py-1.5 sm:w-32" placeholder="Umpan Balik" />
                                        <x-ui.button :submit="true" type="secondary" size="sm">Simpan</x-ui.button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse ($assignmentUsers as $user)
                    @php($userFiles = $assignment->submissions->where('user_id', $user->id))
                    <div class="rounded-lg border border-gray-100 p-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-bold text-gray-600">{{ Str::limit($user->name, 2, '') }}</span>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-900">{{ $user->name }}</p>
                                <p class="mt-0.5"><span class="badge-{{ $user->pivot->status === 'graded' ? 'green' : ($user->pivot->status === 'done' ? 'rose-deep' : 'amber') }}">{{ match ($user->pivot->status) { 'graded' => 'Dinilai', 'done' => 'Selesai', default => 'Tertunda' } }}</span></p>
                            </div>
                        </div>

                        <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2.5 text-sm">
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Dikumpulkan</dt>
                                <dd class="mt-0.5 text-gray-700">{{ $user->pivot->submitted_at?->format('M j, H:i') ?? 'Belum' }}</dd>
                            </div>
                            <div class="text-right">
                                <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Nilai</dt>
                                <dd class="mt-0.5 font-bold text-gray-900">{{ $user->pivot->grade ?? '-' }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Tautan</dt>
                                <dd class="mt-1">
                                    @if ($user->pivot->submission_url)
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $user->pivot->submission_url }}" target="_blank" rel="noopener" class="min-w-0 truncate text-sm font-medium text-rose-700 hover:underline" title="{{ $user->pivot->submission_url }}">{{ $user->pivot->submission_url }}</a>
                                            <form method="POST" action="{{ route('class-rooms.subjects.assignments.submissions.link.destroy', [$classRoom, $subject, $assignment, $user]) }}" data-confirm="Hapus tautan ini?" onsubmit="return confirm(this.dataset.confirm)" class="shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Berkas</dt>
                                <dd class="mt-1">
                                    @forelse ($userFiles as $file)
                                        <div class="flex items-center gap-2 py-0.5">
                                            <a href="{{ route('class-rooms.subjects.assignments.submissions.download', [$classRoom, $subject, $assignment, $file]) }}" class="min-w-0 truncate text-sm font-medium text-rose-700 hover:underline" title="{{ $file->original_name }}">{{ $file->original_name }}</a>
                                            @if ($file->is_late)
                                                <span class="badge-amber shrink-0">Telat</span>
                                            @endif
                                            <form method="POST" action="{{ route('class-rooms.subjects.assignments.submissions.destroy', [$classRoom, $subject, $assignment, $file]) }}" data-confirm="Hapus berkas ini?" onsubmit="return confirm(this.dataset.confirm)" class="shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">Hapus</button>
                                            </form>
                                        </div>
                                    @empty
                                        <span class="text-gray-400">-</span>
                                    @endforelse
                                </dd>
                            </div>
                        </dl>

                        <form method="POST" action="{{ route('class-rooms.subjects.assignments.grade', [$classRoom, $subject, $assignment, $user]) }}" class="mt-3 grid grid-cols-1 gap-2 rounded-md bg-gray-50 p-3 sm:grid-cols-[1fr_1.5fr_auto]">
                            @csrf
                            <div>
                                <label for="grade-{{ $user->id }}" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-400">Nilai</label>
                                <input id="grade-{{ $user->id }}" type="number" name="grade" min="0" max="100" step="0.01" value="{{ $user->pivot->grade }}" class="input w-full py-2" placeholder="Nilai" />
                            </div>
                            <div>
                                <label for="feedback-{{ $user->id }}" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-400">Umpan Balik</label>
                                <input id="feedback-{{ $user->id }}" type="text" name="feedback" value="{{ $user->pivot->feedback }}" class="input w-full py-2" placeholder="Umpan Balik" />
                            </div>
                            <div class="self-end">
                                <x-ui.button :submit="true" type="secondary" class="w-full sm:w-auto">Simpan</x-ui.button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500">Belum ada mahasiswa yang terdaftar di mata pelajaran ini.</p>
                @endforelse
            </div>

            <div class="mt-4 border-t border-gray-100 pt-4">
                {{ $assignmentUsers->links() }}
            </div>
        </x-ui.card>
    @else
        {{-- Student submission view --}}
        @php($myProgress = $assignment->users->where('id', Auth::id())->first()?->pivot)
        @php($myFiles = $assignment->submissions->where('user_id', Auth::id()))
        <div class="card">
            <div class="card-body text-center">
                @if ($myProgress?->status === 'done' || $myProgress?->status === 'graded')
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">Sudah Dikumpulkan</p>
                    <p class="mt-1 text-xs text-gray-500">
                        @if ($myProgress?->grade)
                            Nilai: <strong>{{ $myProgress->grade }}</strong>
                        @endif
                        @if ($myProgress?->feedback)
                            · {{ $myProgress->feedback }}
                        @endif
                        @if ($myProgress?->submitted_at)
                            · {{ $myProgress->submitted_at->format('M j, Y H:i') }}
                        @endif
                    </p>
                @else
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">Belum Dikumpulkan</p>
                    <p class="mt-1 text-xs text-gray-500">
                        @if ($assignment->due_date && $assignment->isLate(now()))
                            Tenggat telah lewat, tetapi kamu masih bisa mengumpulkan dan akan ditandai <strong>telat</strong>.
                        @elseif ($assignment->requiresFile() && $assignment->requiresLink())
                            Unggah berkas dan tautan sesuai ketentuan di bawah untuk mengumpulkan tugas ini.
                        @elseif ($assignment->requiresFile())
                            Unggah berkas sesuai ketentuan di bawah untuk mengumpulkan tugas ini.
                        @elseif ($assignment->requiresLink())
                            Kirimkan tautan pekerjaanmu pada kolom di bawah untuk mengumpulkan tugas ini.
                        @else
                            Tandai tugas ini sebagai selesai setelah kamu menyelesaikannya.
                        @endif
                    </p>
                @endif

                @if ($assignment->requiresFile() || $assignment->requiresLink())
                    @php($submitLabel = $assignment->requiresFile() && $assignment->requiresLink() ? 'Kumpulkan' : ($assignment->requiresFile() ? 'Kumpulkan Berkas' : 'Kumpulkan Tautan'))
                    <form method="POST" enctype="multipart/form-data" action="{{ route('class-rooms.subjects.assignments.submit', [$classRoom, $subject, $assignment]) }}" class="mt-6 rounded-md border border-dashed bg-gray-50 p-4 text-left">
                        @csrf
                        @if ($assignment->requiresFile())
                            <label for="files" class="mb-2 block text-xs font-semibold text-gray-700">Unggah Berkas (maksimum {{ $assignment->max_files }})</label>
                            <input id="files" type="file" name="files[]" multiple class="input" accept=".{{ implode(', .', $assignment->allowedExtensionList()) }}" />
                            @error('files')<p class="form-error">{{ $message }}</p>@enderror
                            @error('files.*')<p class="form-error">{{ $message }}</p>@enderror
                            <p class="mt-2 text-xs text-gray-500">
                                Format yang diperbolehkan:
                                @if ($assignment->allowedExtensionList())
                                    .{{ implode(', .', $assignment->allowedExtensionList()) }}
                                @else
                                    semua format
                                @endif
                                · Maksimum {{ number_format($assignment->max_file_size_kb / 1024, 1) }} MB per berkas.
                            </p>
                        @endif

                        @if ($assignment->requiresLink())
                            <div class="@if ($assignment->requiresFile()) mt-4 @endif">
                                <label for="submission_url" class="mb-2 block text-xs font-semibold text-gray-700">Tautan Pekerjaan</label>
                                <input id="submission_url" type="url" name="submission_url" class="input" value="{{ old('submission_url') }}" placeholder="https://drive.google.com/…" />
                                <p class="mt-2 text-xs text-gray-500">Tempel tautan yang dapat diakses dengan siapa saja.</p>
                                @error('submission_url')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        @endif

                        <div class="mt-4 flex justify-end">
                            <x-ui.button :submit="true" type="success" class="w-full sm:w-auto">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                                {{ $submitLabel }}
                            </x-ui.button>
                        </div>
                    </form>
                @else
                    @if ($myProgress?->status !== 'done' && $myProgress?->status !== 'graded')
                        <form method="POST" action="{{ route('class-rooms.subjects.assignments.submit', [$classRoom, $subject, $assignment]) }}" class="mt-5">
                            @csrf
                            <x-ui.button :submit="true" type="success" class="w-full sm:w-auto">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Tandai Selesai
                            </x-ui.button>
                        </form>
                    @endif
                @endif

                @if ($myFiles->isNotEmpty())
                    <div class="mt-6 text-left">
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Berkas Saya ({{ $myFiles->count() }})</h4>
                        <ul class="space-y-2">
                            @foreach ($myFiles as $file)
                                <li class="flex items-center justify-between gap-2 rounded-md border bg-gray-50 px-3 py-2 text-sm">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                                        <span class="truncate text-gray-800">{{ $file->original_name }}</span>
                                        <span class="shrink-0 text-xs text-gray-400">{{ $file->humanSize() }}</span>
                                        @if ($file->is_late)
                                            <span class="badge-amber">Telat</span>
                                        @endif
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <a href="{{ route('class-rooms.subjects.assignments.submissions.download', [$classRoom, $subject, $assignment, $file]) }}" class="text-xs font-semibold text-rose-700 hover:text-rose-800">Unduh</a>
                                        <form method="POST" action="{{ route('class-rooms.subjects.assignments.submissions.destroy', [$classRoom, $subject, $assignment, $file]) }}" data-confirm="Hapus berkas ini?" onsubmit="return confirm(this.dataset.confirm)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php($myLink = $myProgress?->submission_url)
                @if ($myLink)
                    <div class="mt-6 text-left">
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Tautan Saya</h4>
                        <ul class="space-y-2">
                            <li class="flex items-center justify-between gap-2 rounded-md border bg-gray-50 px-3 py-2 text-sm">
                                <a href="{{ $myLink }}" target="_blank" rel="noopener" class="min-w-0 truncate text-rose-700 hover:underline" title="{{ $myLink }}">{{ $myLink }}</a>
                                <div class="flex shrink-0 items-center gap-2">
                                    <a href="{{ $myLink }}" target="_blank" rel="noopener" class="text-xs font-semibold text-rose-700 hover:text-rose-800">Buka</a>
                                    <form method="POST" action="{{ route('class-rooms.subjects.assignments.submissions.link.destroy', [$classRoom, $subject, $assignment]) }}" data-confirm="Hapus tautan ini?" onsubmit="return confirm(this.dataset.confirm)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-app-layout>