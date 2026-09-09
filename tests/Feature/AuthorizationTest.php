<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\ClassRoom;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_class_rooms_require_komting_role(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->get('/class-rooms')->assertForbidden();
    }

    public function test_komting_other_cannot_update_foreign_class(): void
    {
        $komtingA = User::factory()->komting()->create();
        $komtingB = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komtingA->id]);

        $this->actingAs($komtingB)
            ->put("/class-rooms/{$classRoom->id}", ['name' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_komting_other_cannot_delete_foreign_class(): void
    {
        $komtingA = User::factory()->komting()->create();
        $komtingB = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komtingA->id]);

        $this->actingAs($komtingB)
            ->delete("/class-rooms/{$classRoom->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('class_rooms', ['id' => $classRoom->id]);
    }

    public function test_student_cannot_manage_foreign_subject(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $other = User::factory()->student()->create();

        $this->actingAs($other)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups", ['name' => 'x'])
            ->assertForbidden();
    }

    public function test_student_cannot_grade_foreign_assignment(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'HW',
            'type' => Assignment::TYPE_INDIVIDUAL,
        ]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/grade/{$student->id}")
            ->assertForbidden();
    }

    public function test_student_can_view_their_own_class_room(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $student = User::factory()->student()->create();
        $classRoom->members()->attach($student);

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}")
            ->assertOk();
    }

    public function test_unenrolled_student_cannot_view_class_room(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}")
            ->assertForbidden();
    }

    public function test_enrolled_student_can_browse_subject_groups_and_assignments(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Beta']);
        $group->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'HW',
            'type' => Assignment::TYPE_INDIVIDUAL,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}")
            ->assertOk();

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups")
            ->assertOk()
            ->assertSee('Group Beta');

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments")
            ->assertOk()
            ->assertSee('HW');
    }

    public function test_unenrolled_student_cannot_browse_subject(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}")
            ->assertForbidden();
    }

    public function test_student_class_room_page_does_not_show_admin_management_links(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $classRoom->members()->attach($student);

        $response = $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}")
            ->assertOk();

        // Students must not see komting/admin-only management actions.
        $response->assertDontSee('Semua kelas');
        $response->assertDontSee('>Ubah<');
        $response->assertDontSee('>Anggota<');

        // They should instead get a role-appropriate home link back to their dashboard.
        $response->assertSee('Beranda');
    }

    public function test_student_assignment_list_does_not_show_export_button(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);

        $response = $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments")
            ->assertOk();

        // The export route is komting/admin-only; students must not see the button.
        $response->assertDontSee(route('class-rooms.subjects.export.assignments', [$classRoom, $subject]));

        // Komting (manager) should see it.
        $this->actingAs($komting)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments")
            ->assertOk()
            ->assertSee(route('class-rooms.subjects.export.assignments', [$classRoom, $subject]));
    }

    public function test_assigned_komting_cannot_update_or_delete_their_class_room(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);

        // Class-room editing/deletion is admin-only; even the assigned komting is blocked.
        $this->actingAs($komting)
            ->put("/class-rooms/{$classRoom->id}", ['name' => 'Changed'])
            ->assertForbidden();

        $this->actingAs($komting)
            ->delete("/class-rooms/{$classRoom->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('class_rooms', ['id' => $classRoom->id]);

        // But the assigned komting may still manage the class room's members.
        $this->actingAs($komting)
            ->get("/class-rooms/{$classRoom->id}/members")
            ->assertOk();
    }
}
