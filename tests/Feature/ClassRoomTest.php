<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_class_room_and_assign_komting(): void
    {
        $admin = User::factory()->admin()->create();
        $komting = User::factory()->komting()->create();

        $response = $this->actingAs($admin)->post('/class-rooms', [
            'name' => 'Informatics 2024',
            'code' => 'IF-2024',
            'description' => 'Bachelor of Informatics',
            'komting_id' => $komting->id,
        ]);

        $response->assertRedirect(route('class-rooms.index'));
        $this->assertDatabaseHas('class_rooms', [
            'name' => 'Informatics 2024',
            'code' => 'IF-2024',
            'komting_id' => $komting->id,
        ]);
    }

    public function test_komting_cannot_create_a_class_room(): void
    {
        $komting = User::factory()->komting()->create();

        $response = $this->actingAs($komting)->post('/class-rooms', [
            'name' => 'Informatics 2024',
            'komting_id' => $komting->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('class_rooms', 0);
    }

    public function test_student_cannot_create_a_class_room(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->post('/class-rooms', [
            'name' => 'Informatics 2024',
        ]);

        $response->assertForbidden();
    }

    public function test_komting_can_create_subject_within_class(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);

        $response = $this->actingAs($komting)->post("/class-rooms/{$classRoom->id}/subjects", [
            'name' => 'Software Engineering',
            'code' => 'SE',
            'group_mode' => Subject::GROUP_MODE_RANDOM,
        ]);

        $response->assertRedirect(route('class-rooms.subjects.index', $classRoom));
        $this->assertDatabaseHas('subjects', [
            'class_room_id' => $classRoom->id,
            'name' => 'Software Engineering',
            'group_mode' => Subject::GROUP_MODE_RANDOM,
        ]);
    }

    public function test_komting_can_add_member_to_class_and_subject(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();

        $this->actingAs($komting)->post("/class-rooms/{$classRoom->id}/members", [
            'user_ids' => [$student->id],
        ]);

        $this->assertDatabaseHas('class_user', [
            'class_room_id' => $classRoom->id,
            'user_id' => $student->id,
        ]);

        $this->actingAs($komting)->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/members", [
            'user_ids' => [$student->id],
        ]);

        $this->assertDatabaseHas('subject_user', [
            'subject_id' => $subject->id,
            'user_id' => $student->id,
        ]);
    }
}
