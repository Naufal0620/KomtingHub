<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_komting_can_create_assignment_with_progress_rows(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $students = User::factory()->count(3)->student()->create();
        $subject->members()->attach($students->pluck('id'));

        $response = $this->actingAs($komting)->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
            'title' => 'Final Project',
            'description' => 'Build a system',
            'type' => Assignment::TYPE_INDIVIDUAL,
        ]);

        $response->assertRedirect();

        $assignment = $subject->assignments()->first();
        $this->assertNotNull($assignment);
        $this->assertEquals(3, $assignment->users()->count());
        $this->assertEquals(Assignment::STATUS_PENDING, $assignment->users()->first()->pivot->status);
    }

    public function test_student_can_submit_assignment(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Homework 1',
            'type' => Assignment::TYPE_INDIVIDUAL,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit")
            ->assertRedirect();

        $this->assertEquals(Assignment::STATUS_DONE, $assignment->users()->whereKey($student->id)->first()->pivot->status);
        $this->assertNotNull($assignment->users()->whereKey($student->id)->first()->pivot->submitted_at);
    }

    public function test_komting_can_grade_assignment(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Homework 1',
            'type' => Assignment::TYPE_INDIVIDUAL,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_DONE]);

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/grade/{$student->id}", [
                'grade' => 85.5,
                'feedback' => 'Great work!',
            ])
            ->assertRedirect();

        $pivot = $assignment->users()->whereKey($student->id)->first()->pivot;
        $this->assertEquals(Assignment::STATUS_GRADED, $pivot->status);
        $this->assertEquals(85.5, $pivot->grade);
        $this->assertEquals('Great work!', $pivot->feedback);
    }

    public function test_komting_can_configure_file_submission(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
                'title' => 'Laporan Akhir',
                'description' => 'Unggah laporan dalam format pdf atau docx.',
                'type' => Assignment::TYPE_INDIVIDUAL,
                'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
                'max_files' => 3,
                'allowed_extensions' => ['pdf', 'docx'],
                'max_file_size_kb' => 2048,
            ])
            ->assertRedirect();

        $assignment = $subject->assignments()->first();
        $this->assertNotNull($assignment);
        $this->assertEquals(Assignment::SUBMISSION_MODE_FILE, $assignment->submission_mode);
        $this->assertEquals(3, $assignment->max_files);
        $this->assertSame(['pdf', 'docx'], $assignment->allowedExtensionList());
        $this->assertEquals(2048, $assignment->max_file_size_kb);
    }

    public function test_student_can_upload_multiple_files_to_assignment(): void
    {
        Storage::fake('submissions');

        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Homework 2',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 2,
            'allowed_extensions' => 'pdf,docx',
            'max_file_size_kb' => 10240,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [
                    UploadedFile::fake()->create('report.pdf', 100),
                    UploadedFile::fake()->create('appendix.docx', 100),
                ],
            ])
            ->assertRedirect();

        $this->assertSame(2, $assignment->fresh()->submissions()->count());
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'report.pdf',
            'is_late' => false,
        ]);
        $report = $assignment->fresh()->submissions()->where('original_name', 'report.pdf')->first();
        $this->assertStringStartsNotWith('/..', $report->path);
        Storage::disk('submissions')->assertExists($report->path);

        $pivot = $assignment->users()->whereKey($student->id)->first()->pivot;
        $this->assertEquals(Assignment::STATUS_DONE, $pivot->status);
    }

    public function test_upload_after_deadline_is_marked_late(): void
    {
        Storage::fake('submissions');

        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Late Assignment',
            'due_date' => now()->subDay(),
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 1,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [UploadedFile::fake()->create('late.pdf', 50)],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'late.pdf',
            'is_late' => true,
        ]);
    }

    public function test_submission_rejects_disallowed_extension(): void
    {
        Storage::fake('submissions');

        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Strict Assignment',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 1,
            'allowed_extensions' => 'pdf',
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [UploadedFile::fake()->create('note.txt', 50)],
            ])
            ->assertSessionHasErrors('files.0');

        $this->assertDatabaseCount('assignment_submissions', 0);
    }

    public function test_assignment_show_renders_upload_ui_for_student(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Report 1',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 2,
            'allowed_extensions' => 'pdf,docx',
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('Kumpulkan Berkas')
            ->assertSee('.pdf')
            ->assertSee('.docx');
    }

    public function test_assignment_show_marks_late_files_for_komting(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Report 2',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_DONE]);
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'terlambat.pdf',
            'path' => 'assignments/x.pdf',
            'size' => 100,
            'is_late' => true,
        ]);

        $this->actingAs($komting)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('terlambat.pdf')
            ->assertSee('Telat');
    }

    public function test_komting_can_configure_link_submission(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
                'title' => 'Praktikum',
                'type' => Assignment::TYPE_INDIVIDUAL,
                'submission_mode' => Assignment::SUBMISSION_MODE_LINK,
            ])
            ->assertRedirect();

        $assignment = $subject->assignments()->first();
        $this->assertNotNull($assignment);
        $this->assertEquals(Assignment::SUBMISSION_MODE_LINK, $assignment->submission_mode);
        $this->assertTrue($assignment->requiresLink());
        $this->assertFalse($assignment->requiresFile());
    }

    public function test_komting_can_configure_file_and_link_submission(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);

        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments", [
                'title' => 'Laporan',
                'type' => Assignment::TYPE_INDIVIDUAL,
                'submission_mode' => Assignment::SUBMISSION_MODE_FILE_LINK,
                'max_files' => 2,
                'max_file_size_kb' => 2048,
            ])
            ->assertRedirect();

        $assignment = $subject->assignments()->first();
        $this->assertNotNull($assignment);
        $this->assertEquals(Assignment::SUBMISSION_MODE_FILE_LINK, $assignment->submission_mode);
        $this->assertTrue($assignment->requiresFile());
        $this->assertTrue($assignment->requiresLink());
        $this->assertEquals(2, $assignment->max_files);
    }

    public function test_student_can_submit_link_assignment(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Linkable',
            'submission_mode' => Assignment::SUBMISSION_MODE_LINK,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'submission_url' => 'https://drive.example.com/report',
            ])
            ->assertRedirect();

        $pivot = $assignment->users()->whereKey($student->id)->first()->pivot;
        $this->assertEquals(Assignment::STATUS_DONE, $pivot->status);
        $this->assertEquals('https://drive.example.com/report', $pivot->submission_url);
    }

    public function test_link_submission_is_required(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Strict Link',
            'submission_mode' => Assignment::SUBMISSION_MODE_LINK,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [])
            ->assertSessionHasErrors('submission_url');

        $pivot = $assignment->users()->whereKey($student->id)->first()->pivot;
        $this->assertEquals(Assignment::STATUS_PENDING, $pivot->status);
        $this->assertNull($pivot->submission_url);
    }

    public function test_student_can_submit_file_and_link_assignment(): void
    {
        Storage::fake('submissions');

        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Full',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE_LINK,
            'max_files' => 1,
            'allowed_extensions' => 'pdf',
            'max_file_size_kb' => 10240,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [UploadedFile::fake()->create('report.pdf', 100)],
                'submission_url' => 'https://drive.example.com/report',
            ])
            ->assertRedirect();

        $pivot = $assignment->users()->whereKey($student->id)->first()->pivot;
        $this->assertEquals(Assignment::STATUS_DONE, $pivot->status);
        $this->assertEquals('https://drive.example.com/report', $pivot->submission_url);
        $this->assertDatabaseCount('assignment_submissions', 1);
    }

    public function test_student_can_delete_submitted_link(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Linkable',
            'submission_mode' => Assignment::SUBMISSION_MODE_LINK,
        ]);
        $assignment->users()->attach($student->id, [
            'status' => Assignment::STATUS_DONE,
            'submission_url' => 'https://drive.example.com/report',
        ]);

        $this->actingAs($student)
            ->delete("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submissions/link")
            ->assertRedirect();

        $this->assertNull($assignment->users()->whereKey($student->id)->first()->pivot->submission_url);
    }

    public function test_assignment_show_renders_link_ui_for_student(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Tautan 1',
            'submission_mode' => Assignment::SUBMISSION_MODE_LINK,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('Kumpulkan Tautan')
            ->assertSee('Pengumpulan tautan');
    }

    public function test_non_komting_cannot_grade(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $student = User::factory()->student()->create();
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Homework 1',
            'type' => Assignment::TYPE_INDIVIDUAL,
        ]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/grade/{$student->id}")
            ->assertForbidden();
    }
}
