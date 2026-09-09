<x-app-layout>
    <x-slot name="title">Profil</x-slot>

    <x-ui.page-header title="Profil" subtitle="Kelola akun kamu" back-href="{{ route('dashboard') }}" back-label="Dashboard" />

    <div class="mx-auto max-w-2xl space-y-6">
        <div class="card">
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card ring-red-100">
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
