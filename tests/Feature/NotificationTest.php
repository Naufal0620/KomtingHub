<?php

namespace Tests\Feature;

use App\Mail\AssignmentCreatedMail;
use App\Mail\GroupStatusMail;
use App\Models\Assignment;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: ClassRoom, 2: Subject, 3: \Illuminate\Support\Collection<int, User>}
     */
    private function createSchool(): array
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $students = collect(User::factory()->count(3)->student()->create());
        $subject->members()->attach($students->pluck('id'));

        return [$komting, $classRoom, $subject, $students];
    }

    public function test_assignment_creation_notifies_all_members_synchronously(): void
    {
        Mail::fake();
        [$komting, $classRoom, $subject, $students] = $this->createSchool();

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
                'title' => 'Tugas A',
                'type' => Assignment::TYPE_INDIVIDUAL,
            ])
            ->assertRedirect();

        foreach ($students as $student) {
            $this->assertSame(1, $student->notifications()->count());
            $this->assertDatabaseHas('notifications', [
                'type' => 'App\Notifications\AssignmentCreatedNotification',
                'notifiable_id' => $student->id,
            ]);
        }

        Mail::assertQueued(AssignmentCreatedMail::class, 3);
    }

    public function test_group_lock_and_unlock_notify_members(): void
    {
        Mail::fake();
        [$komting, $classRoom, $subject, $students] = $this->createSchool();

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups-lock")
            ->assertRedirect();

        $student = $students->first();
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\GroupLockedNotification',
            'notifiable_id' => $student->id,
        ]);

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups-unlock")
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\GroupUnlockedNotification',
            'notifiable_id' => $student->id,
        ]);

        Mail::assertQueued(GroupStatusMail::class, 6);
    }

    public function test_late_enrolled_member_is_backfilled_with_existing_assignments(): void
    {
        Mail::fake();
        [$komting, $classRoom, $subject] = $this->createSchool();

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
                'title' => 'Tugas A',
                'type' => Assignment::TYPE_INDIVIDUAL,
            ]);

        $late = User::factory()->student()->create();
        $classRoom->members()->attach($late->id);

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/members", [
                'user_ids' => [$late->id],
            ]);

        $this->assertSame(1, $late->notifications()->count());
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\AssignmentCreatedNotification',
            'notifiable_id' => $late->id,
        ]);
        Mail::assertQueued(AssignmentCreatedMail::class, 4);
    }

    public function test_notification_carries_actionable_url(): void
    {
        Mail::fake();
        [$komting, $classRoom, $subject, $students] = $this->createSchool();

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
                'title' => 'Tugas URL',
                'type' => Assignment::TYPE_INDIVIDUAL,
            ]);

        $data = $students->first()->notifications()->first()->data;

        $this->assertArrayHasKey('url', $data);
        $this->assertStringContainsString("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/", $data['url']);
    }

    public function test_mark_as_read_is_scoped_to_authenticated_user(): void
    {
        [$komting, $classRoom, $subject, $students] = $this->createSchool();

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
                'title' => 'Tugas B',
                'type' => Assignment::TYPE_INDIVIDUAL,
            ]);

        [$studentA, $studentB] = $students;
        $foreignNotification = $studentA->notifications()->first();

        $this->actingAs($studentB)
            ->post("/notifications/{$foreignNotification->id}/read")
            ->assertNotFound();
    }

    public function test_user_can_clear_and_delete_own_notifications(): void
    {
        [$komting, $classRoom, $subject, $students] = $this->createSchool();

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
                'title' => 'Tugas C',
                'type' => Assignment::TYPE_INDIVIDUAL,
            ]);

        [$studentA, $studentB] = $students;

        $this->actingAs($studentA)
            ->delete("/notifications/{$studentA->notifications()->first()->id}")
            ->assertRedirect();

        $this->assertSame(0, $studentA->fresh()->notifications()->count());

        $this->actingAs($studentB)
            ->delete('/notifications/clear-all')
            ->assertRedirect();

        $this->assertSame(0, $studentB->fresh()->notifications()->count());
    }
}