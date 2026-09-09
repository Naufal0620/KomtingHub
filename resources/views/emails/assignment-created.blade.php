@component('mail::message')
Halo!

Sebuah tugas baru telah dibuat: **{{ $assignment->title }}**

@if ($assignment->description)
{{ $assignment->description }}
@endif

@if ($assignment->due_date)
Tenggat: {{ $assignment->due_date->format('d M Y') }}
@endif

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Lihat Tugas
@endcomponent

Salam,<br>
{{ config('app.name') }}
@endcomponent