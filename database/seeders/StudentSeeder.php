<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Daftar akun mahasiswa yang akan dibuat.
     *
     * Tambahkan baris berikut sesuai kebutuhan:
     * ['name' => 'Nama Mahasiswa', 'email' => 'email@example.com'],
     */
    protected array $students = [
        ['name' => 'Mahasiswa 1', 'email' => 'mhs1@example.com'],
        ['name' => 'Mahasiswa 2', 'email' => 'mhs2@example.com'],
        ['name' => 'Mahasiswa 3', 'email' => 'mhs3@example.com'],
        ['name' => 'Mahasiswa 4', 'email' => 'mhs4@example.com'],
        ['name' => 'Mahasiswa 5', 'email' => 'mhs5@example.com'],
        ['name' => 'Mahasiswa 6', 'email' => 'mhs6@example.com'],
        ['name' => 'Mahasiswa 7', 'email' => 'mhs7@example.com'],
        ['name' => 'Mahasiswa 8', 'email' => 'mhs8@example.com'],
        ['name' => 'Mahasiswa 9', 'email' => 'mhs9@example.com'],
        ['name' => 'Mahasiswa 10', 'email' => 'mhs10@example.com'],
        ['name' => 'Mahasiswa 11', 'email' => 'mhs11@example.com'],
        ['name' => 'Mahasiswa 12', 'email' => 'mhs12@example.com'],
        ['name' => 'Mahasiswa 13', 'email' => 'mhs13@example.com'],
        ['name' => 'Mahasiswa 14', 'email' => 'mhs14@example.com'],
        ['name' => 'Mahasiswa 15', 'email' => 'mhs15@example.com'],
        ['name' => 'Mahasiswa 16', 'email' => 'mhs16@example.com'],
        ['name' => 'Mahasiswa 17', 'email' => 'mhs17@example.com'],
        ['name' => 'Mahasiswa 18', 'email' => 'mhs18@example.com'],
        ['name' => 'Mahasiswa 19', 'email' => 'mhs19@example.com'],
        ['name' => 'Mahasiswa 20', 'email' => 'mhs20@example.com'],
        ['name' => 'Mahasiswa 21', 'email' => 'mhs21@example.com'],
        ['name' => 'Mahasiswa 22', 'email' => 'mhs22@example.com'],
        ['name' => 'Mahasiswa 23', 'email' => 'mhs23@example.com'],
        ['name' => 'Mahasiswa 24', 'email' => 'mhs24@example.com'],
        ['name' => 'Mahasiswa 25', 'email' => 'mhs25@example.com'],
        ['name' => 'Mahasiswa 26', 'email' => 'mhs26@example.com'],
        ['name' => 'Mahasiswa 27', 'email' => 'mhs27@example.com'],
        ['name' => 'Mahasiswa 28', 'email' => 'mhs28@example.com'],
        ['name' => 'Mahasiswa 29', 'email' => 'mhs29@example.com'],
        ['name' => 'Mahasiswa 30', 'email' => 'mhs30@example.com'],
        ['name' => 'Mahasiswa 31', 'email' => 'mhs31@example.com'],
        ['name' => 'Mahasiswa 32', 'email' => 'mhs32@example.com'],
        ['name' => 'Mahasiswa 33', 'email' => 'mhs33@example.com'],
        ['name' => 'Mahasiswa 34', 'email' => 'mhs34@example.com'],
        ['name' => 'Mahasiswa 35', 'email' => 'mhs35@example.com'],
        ['name' => 'Mahasiswa 36', 'email' => 'mhs36@example.com'],
        ['name' => 'Mahasiswa 37', 'email' => 'mhs37@example.com'],
        ['name' => 'Mahasiswa 38', 'email' => 'mhs38@example.com'],
        ['name' => 'Mahasiswa 39', 'email' => 'mhs39@example.com'],
    ];

    /**
     * Seed the application's student accounts.
     */
    public function run(): void
    {
        foreach ($this->students as $student) {
            User::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'role' => User::ROLE_STUDENT,
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
