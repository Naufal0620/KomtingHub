<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_endpoint_is_http_throttled(): void
    {
        $user = User::factory()->student()->create(['password' => bcrypt('secret123')]);

        for ($i = 0; $i < 20; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertStatus(429);
    }

    public function test_submission_upload_is_throttled(): void
    {
        Storage::fake('submissions');

        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Bulk',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 5,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($student)
                ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                    'files' => [UploadedFile::fake()->create("f{$i}.pdf", 10)],
                ]);
        }

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [UploadedFile::fake()->create('blocked.pdf', 10)],
            ])
            ->assertStatus(429);
    }

    public function test_uploaded_file_is_not_exposed_on_public_disk(): void
    {
        Storage::fake('submissions');
        Storage::fake('public');

        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Rahasia',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 1,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [UploadedFile::fake()->create('privat.pdf', 50)],
            ])
            ->assertRedirect();

        $submission = $assignment->submissions()->first();
        Storage::disk('submissions')->assertExists($submission->path);
        Storage::disk('public')->assertMissing($submission->path);
    }

    public function test_non_enrolled_student_cannot_submit(): void
    {
        Storage::fake('submissions');

        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Terlarang',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 1,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [UploadedFile::fake()->create('a.pdf', 50)],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('assignment_submissions', 0);
    }
}
