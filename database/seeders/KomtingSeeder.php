<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KomtingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's default komting account.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'komting@example.com'],
            [
                'name' => 'Komting',
                'role' => User::ROLE_KOMTING,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
    }
}
