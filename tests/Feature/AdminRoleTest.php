<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\ClassRoom;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_lists_all_classes(): void
    {
        $admin = User::factory()->admin()->create();
        $komtingA = User::factory()->komting()->create();
        $komtingB = User::factory()->komting()->create();

        ClassRoom::factory()->create(['komting_id' => $komtingA->id, 'name' => 'Class A']);
        ClassRoom::factory()->create(['komting_id' => $komtingB->id, 'name' => 'Class B']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Class A')
            ->assertSee('Class B');
    }

    public function test_admin_can_access_class_rooms_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/class-rooms')->assertOk();
    }

    public function test_admin_can_view_foreign_class_room(): void
    {
        $admin = User::factory()->admin()->create();
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);

        $this->actingAs($admin)->get("/class-rooms/{$classRoom->id}")->assertOk();
    }

    public function test_admin_can_update_foreign_class_room(): void
    {
        $admin = User::factory()->admin()->create();
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);

        $this->actingAs($admin)
            ->put("/class-rooms/{$classRoom->id}", [
                'name' => 'Renamed',
                'komting_id' => $komting->id,
            ])
            ->assertRedirect(route('class-rooms.index'));

        $this->assertDatabaseHas('class_rooms', ['id' => $classRoom->id, 'name' => 'Renamed']);
    }

    public function test_admin_can_delete_foreign_class_room(): void
    {
        $admin = User::factory()->admin()->create();
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);

        $this->actingAs($admin)
            ->delete("/class-rooms/{$classRoom->id}")
            ->assertRedirect(route('class-rooms.index'));

        $this->assertDatabaseMissing('class_rooms', ['id' => $classRoom->id]);
    }

    public function test_admin_can_manage_foreign_subject_groups(): void
    {
        $admin = User::factory()->admin()->create();
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);

        $this->actingAs($admin)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups", ['name' => 'Team A'])
            ->assertRedirect(route('class-rooms.subjects.groups.index', [$classRoom, $subject]));

        $this->assertDatabaseHas('groups', ['subject_id' => $subject->id, 'name' => 'Team A']);
    }

    public function test_admin_can_grade_foreign_assignment(): void
    {
        $admin = User::factory()->admin()->create();
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'HW',
            'type' => Assignment::TYPE_INDIVIDUAL,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($admin)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/grade/{$student->id}", [
                'grade' => 90,
                'feedback' => 'Great work',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assignment_user', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'grade' => 90,
        ]);
    }

    public function test_admin_can_access_management_routes_through_role_middleware(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/class-rooms/create')->assertOk();
    }

    public function test_komting_dashboard_renders_with_classes(): void
    {
        $komting = User::factory()->komting()->create();
        ClassRoom::factory()->create(['komting_id' => $komting->id, 'name' => 'My Class']);

        $this->actingAs($komting)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('My Class');
    }
}
