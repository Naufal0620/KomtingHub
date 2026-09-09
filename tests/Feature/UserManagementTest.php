<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_public_registration_route_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_guest_is_redirected_to_login_from_user_management(): void
    {
        $this->get('/users')->assertRedirect(route('login'));
    }

    public function test_komting_cannot_access_user_management(): void
    {
        $komting = User::factory()->komting()->create();

        $this->actingAs($komting)->get('/users')->assertForbidden();
    }

    public function test_admin_can_list_users(): void
    {
        $admin = $this->admin();
        User::factory()->komting()->create(['name' => 'Komting Senior']);
        User::factory()->student()->create(['name' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Komting Senior');
    }

    public function test_admin_can_create_komting_account(): void
    {
        $this->actingAs($this->admin())
            ->post('/users', [
                'name' => 'Komting Baru',
                'email' => 'komting2@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'komting',
            ])
            ->assertRedirect(route('users.index', ['role' => 'komting']));

        $this->assertDatabaseHas('users', [
            'email' => 'komting2@example.com',
            'role' => User::ROLE_KOMTING,
        ]);
    }

    public function test_admin_can_create_student_account(): void
    {
        $this->actingAs($this->admin())
            ->post('/users', [
                'name' => 'Mahasiswa Baru',
                'email' => 'mhs-baru@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'student',
            ])
            ->assertRedirect(route('users.index', ['role' => 'student']));

        $this->assertDatabaseHas('users', [
            'email' => 'mhs-baru@example.com',
            'role' => User::ROLE_STUDENT,
        ]);
    }

    public function test_admin_cannot_create_admin_account_via_form(): void
    {
        $this->actingAs($this->admin())
            ->post('/users', [
                'name' => 'Bogus Admin',
                'email' => 'bogus@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => User::ROLE_ADMIN,
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'bogus@example.com']);
    }

    public function test_email_must_be_unique(): void
    {
        $existing = User::factory()->student()->create(['email' => 'same@example.com']);

        $this->actingAs($this->admin())
            ->post('/users', [
                'name' => 'Duplikat',
                'email' => 'same@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'student',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', ['id' => $existing->id]);
    }

    public function test_admin_can_update_account_and_change_role(): void
    {
        $this->actingAs($this->admin());
        $student = User::factory()->student()->create([
            'name' => 'Lama',
            'email' => 'lama@example.com',
        ]);

        $this->put("/users/{$student->id}", [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'komting',
        ])->assertRedirect(route('users.index', ['role' => 'komting']));

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'role' => User::ROLE_KOMTING,
        ]);
    }

    public function test_admin_can_reset_password_through_update(): void
    {
        $this->actingAs($this->admin());
        $student = User::factory()->student()->create();

        $this->put("/users/{$student->id}", [
            'name' => $student->name,
            'email' => $student->email,
            'password' => 'rahasia-baru',
            'password_confirmation' => 'rahasia-baru',
            'role' => 'student',
        ])->assertRedirect();

        $this->assertTrue(
            password_verify('rahasia-baru', $student->fresh()->password)
        );
    }

    public function test_admin_cannot_manage_another_admin_account(): void
    {
        $admin = $this->admin();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get("/users/{$otherAdmin->id}/edit")
            ->assertNotFound();

        $this->actingAs($admin)
            ->put("/users/{$otherAdmin->id}", [
                'name' => 'Hacked',
                'email' => $otherAdmin->email,
                'password' => '',
                'password_confirmation' => '',
                'role' => 'student',
            ])
            ->assertNotFound();
    }

    public function test_admin_can_view_create_and_edit_pages(): void
    {
        $admin = $this->admin();
        $student = User::factory()->student()->create();

        $this->actingAs($admin)
            ->get('/users/create')
            ->assertOk()
            ->assertSee('Tambah Akun');

        $this->actingAs($admin)
            ->get("/users/{$student->id}/edit")
            ->assertOk()
            ->assertSee('Edit Akun');
    }

    public function test_admin_can_delete_account(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($this->admin())
            ->delete("/users/{$student->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete("/users/{$admin->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}