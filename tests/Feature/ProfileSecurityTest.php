<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_middleware_blocks_unverified_users_from_profile(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect('/verify-email');
    }

    public function test_email_change_resets_verification_and_forces_verification_screen(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => 'new@example.com',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->email_verified_at);

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect('/verify-email');
    }

    public function test_admin_created_user_is_pre_verified(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Mahasiswa Baru',
                'email' => 'baru@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => User::ROLE_STUDENT,
            ])
            ->assertRedirect();

        $created = $admin->isAdmin() ? User::where('email', 'baru@example.com')->firstOrFail() : null;

        $this->assertNotNull($created->email_verified_at);
    }

    public function test_last_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete('/profile', ['password' => 'password'])
            ->assertForbidden();

        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_can_delete_own_account_when_another_admin_exists(): void
    {
        User::factory()->admin()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertNull($admin->fresh());
    }

    public function test_notifications_are_cleaned_up_on_account_deletion(): void
    {
        $user = User::factory()->create();
        $user->notify(new DeleteMeNotification);

        $this->assertSame(1, $user->notifications()->count());

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ]);
    }
}

class DeleteMeNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['message' => 'delete me'];
    }
}
