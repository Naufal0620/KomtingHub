<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    private function setupSubject(string $mode = Subject::GROUP_MODE_SELECT): array
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create([
            'class_room_id' => $classRoom->id,
            'group_mode' => $mode,
        ]);
        $students = User::factory()->count(4)->student()->create();
        $subject->members()->attach($students->pluck('id'));

        return [$komting, $classRoom, $subject, $students];
    }

    public function test_komting_can_create_group(): void
    {
        [$komting, $classRoom, $subject] = $this->setupSubject();

        $response = $this->actingAs($komting)->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups", [
            'name' => 'Group Alpha',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'subject_id' => $subject->id,
            'name' => 'Group Alpha',
        ]);
    }

    public function test_student_can_join_and_leave_group_in_self_selection_mode(): void
    {
        [$komting, $classRoom, $subject, $students] = $this->setupSubject();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        $student = $students->first();

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/join")
            ->assertRedirect();

        $this->assertTrue($group->members()->whereKey($student->id)->exists());

        $this->actingAs($student)
            ->delete("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/leave")
            ->assertRedirect();

        $this->assertFalse($group->members()->whereKey($student->id)->exists());
    }

    public function test_student_cannot_join_group_when_locked(): void
    {
        [$komting, $classRoom, $subject, $students] = $this->setupSubject();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        $student = $students->first();

        $subject->update(['groups_locked' => true]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/join")
            ->assertForbidden();
    }

    public function test_student_cannot_self_select_in_random_mode(): void
    {
        [$komting, $classRoom, $subject, $students] = $this->setupSubject(Subject::GROUP_MODE_RANDOM);
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        $student = $students->first();

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/join")
            ->assertForbidden();
    }

    public function test_komting_can_lock_groups(): void
    {
        [$komting, $classRoom, $subject] = $this->setupSubject();

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups-lock")
            ->assertRedirect();

        $this->assertTrue($subject->fresh()->groups_locked);
    }

    public function test_komting_can_add_member_to_group(): void
    {
        [$komting, $classRoom, $subject, $students] = $this->setupSubject();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        [$studentA, $studentB] = $students->take(2);

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/members", [
                'user_ids' => [$studentA->id, $studentB->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($group->members()->whereKey($studentA->id)->exists());
        $this->assertTrue($group->members()->whereKey($studentB->id)->exists());
    }

    public function test_komting_can_remove_member_from_group(): void
    {
        [$komting, $classRoom, $subject, $students] = $this->setupSubject();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        $student = $students->first();
        $group->members()->attach($student->id);

        $this->actingAs($komting)
            ->delete("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/members/{$student->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($group->members()->whereKey($student->id)->exists());
    }

    public function test_komting_cannot_add_non_subject_member_to_group(): void
    {
        [$komting, $classRoom, $subject] = $this->setupSubject();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        $outsider = User::factory()->student()->create();

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/members", [
                'user_ids' => [$outsider->id],
            ])
            ->assertStatus(422);

        $this->assertFalse($group->members()->whereKey($outsider->id)->exists());
    }

    public function test_student_cannot_manage_group_members(): void
    {
        [$komting, $classRoom, $subject, $students] = $this->setupSubject();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        $student = $students->first();

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/members", [
                'user_ids' => [$student->id],
            ])
            ->assertForbidden();
    }
}
