@component('mail::message')
Halo!

@if ($locked)
Kelompok kamu untuk mata pelajaran **{{ $subject->name }}** telah dikunci.
Keanggotaan kelompok tidak dapat diubah sampai kelompok dibuka kembali.
@else
Kelompok kamu untuk mata pelajaran **{{ $subject->name }}** telah dibuka kembali.
Kamu dapat mengubah keanggotaan kelompok kamu jika mata pelajaran menggunakan mode pilihan mandiri.
@endif

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Lihat Mata Pelajaran
@endcomponent

Salam,<br>
{{ config('app.name') }}
@endcomponent