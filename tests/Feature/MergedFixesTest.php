<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassRoom;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MergedFixesTest extends TestCase
{
    use RefreshDatabase;

    private function createSchool(): array
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create([
            'class_room_id' => $classRoom->id,
            'group_mode' => Subject::GROUP_MODE_SELECT,
        ]);

        return [$komting, $classRoom, $subject];
    }

    private function enrollStudent(Subject $subject): User
    {
        $student = User::factory()->student()->create();
        $subject->members()->attach($student);

        return $student;
    }

    public function test_admin_created_student_can_hit_dashboard_without_verifying_email(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/users', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_STUDENT,
        ])->assertRedirect();

        $student = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($student->email_verified_at);

        $this->actingAs($student)->get('/dashboard')->assertOk();
    }

    public function test_komting_with_managed_class_rooom_cannot_be_demoted(): void
    {
        $admin = User::factory()->admin()->create();
        $komting = User::factory()->komting()->create();
        ClassRoom::factory()->create(['komting_id' => $komting->id]);

        $this->actingAs($admin)
            ->put("/users/{$komting->id}", [
                'name' => $komting->name,
                'email' => $komting->email,
                'role' => User::ROLE_STUDENT,
            ])
            ->assertStatus(422);

        $this->assertEquals(User::ROLE_KOMTING, $komting->fresh()->role);
    }

    public function test_komting_managing_class_rooom_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $komting = User::factory()->komting()->create();
        ClassRoom::factory()->create(['komting_id' => $komting->id]);

        $this->actingAs($admin)
            ->delete("/users/{$komting->id}")
            ->assertStatus(422);

        $this->assertNotNull($komting->fresh());
    }

    public function test_student_cannot_join_or_leave_group(): void
    {
        [, $classRoom, $subject] = $this->createSchool();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Alpha']);

        $this->actingAs($subject->classRoom->komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/join")
            ->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups/{$group->id}/join")
            ->assertForbidden();

        $this->assertFalse($group->members()->whereKey($subject->classRoom->komting->id)->exists());
    }

    public function test_lock_sets_locked_at_on_all_memberships(): void
    {
        [, $classRoom, $subject] = $this->createSchool();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Alpha']);
        $student = $this->enrollStudent($subject);
        $group->members()->attach($student->id);

        $komting = $subject->classRoom->komting;
        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups-lock")
            ->assertRedirect();

        $this->assertTrue($subject->fresh()->groups_locked);
        $this->assertNotNull(DB::table('group_user')->where('group_id', $group->id)->value('locked_at'));
    }

    public function test_class_room_member_removal_cleans_up_subject_groups_and_files(): void
    {
        Storage::fake('submissions');

        [, $classRoom, $subject] = $this->createSchool();
        $student = $this->enrollStudent($subject);
        $classRoom->members()->attach($student->id);

        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Alpha']);
        $group->members()->attach($student->id);

        $assignment = Assignment::create(['subject_id' => $subject->id, 'title' => 'T1']);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_DONE]);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'x.pdf',
            'path' => 'assignments/'.$assignment->id.'/'.$student->id.'/x.pdf',
            'size' => 100,
            'mime' => 'application/pdf',
        ]);
        Storage::disk('submissions')->put($submission->path, 'data');

        $komting = $subject->classRoom->komting;
        $this->actingAs($komting)
            ->delete("/class-rooms/{$classRoom->id}/members/{$student->id}")
            ->assertRedirect();

        $this->assertFalse($classRoom->members()->whereKey($student->id)->exists());
        $this->assertFalse($subject->members()->whereKey($student->id)->exists());
        $this->assertFalse($group->members()->whereKey($student->id)->exists());
        $this->assertDatabaseMissing('assignment_user', ['user_id' => $student->id]);
        $this->assertDatabaseMissing('assignment_submissions', ['user_id' => $student->id]);
        Storage::disk('submissions')->assertMissing($submission->path);
    }

    public function test_subject_member_removal_cleans_up_group_and_submission(): void
    {
        Storage::fake('submissions');

        [, $classRoom, $subject] = $this->createSchool();
        $student = $this->enrollStudent($subject);

        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Alpha']);
        $group->members()->attach($student->id);

        $assignment = Assignment::create(['subject_id' => $subject->id, 'title' => 'T1']);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_DONE]);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'x.pdf',
            'path' => 'assignments/'.$assignment->id.'/'.$student->id.'/x.pdf',
            'size' => 100,
            'mime' => 'application/pdf',
        ]);
        Storage::disk('submissions')->put($submission->path, 'data');

        $komting = $subject->classRoom->komting;
        $this->actingAs($komting)
            ->delete("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/members/{$student->id}")
            ->assertRedirect();

        $this->assertFalse($subject->members()->whereKey($student->id)->exists());
        $this->assertFalse($group->members()->whereKey($student->id)->exists());
        $this->assertDatabaseMissing('assignment_user', ['user_id' => $student->id]);
        $this->assertDatabaseMissing('assignment_submissions', ['user_id' => $student->id]);
        Storage::disk('submissions')->assertMissing($submission->path);
    }

    public function test_student_cannot_browse_subject_groups_when_not_enrolled(): void
    {
        [, $classRoom, $subject] = $this->createSchool();
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups")
            ->assertForbidden();
    }

    public function test_group_name_duplicate_is_rejected_within_subject(): void
    {
        [, $classRoom, $subject] = $this->createSchool();
        Group::create(['subject_id' => $subject->id, 'name' => 'Alpha']);

        $komting = $subject->classRoom->komting;
        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/groups", [
                'name' => 'Alpha',
            ])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, $subject->groups()->count());

        $otherSubject = Subject::factory()->create(['class_room_id' => $classRoom->id]);
        $this->actingAs($komting)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$otherSubject->id}/groups", [
                'name' => 'Alpha',
            ])
            ->assertRedirect();
    }

    public function test_group_mode_cannot_change_once_groups_exist(): void
    {
        [, $classRoom, $subject] = $this->createSchool();
        Group::create(['subject_id' => $subject->id, 'name' => 'Alpha']);

        $komting = $subject->classRoom->komting;
        $this->actingAs($komting)
            ->put("/class-rooms/{$classRoom->id}/subjects/{$subject->id}", [
                'name' => $subject->name,
                'group_mode' => Subject::GROUP_MODE_RANDOM,
            ])
            ->assertStatus(422);

        $this->assertEquals(Subject::GROUP_MODE_SELECT, $subject->fresh()->group_mode);
    }

    public function test_group_mode_cannot_change_when_groups_are_locked(): void
    {
        [, $classRoom, $subject] = $this->createSchool();
        $subject->update(['groups_locked' => true]);

        $komting = $subject->classRoom->komting;
        $this->actingAs($komting)
            ->put("/class-rooms/{$classRoom->id}/subjects/{$subject->id}", [
                'name' => $subject->name,
                'group_mode' => Subject::GROUP_MODE_RANDOM,
            ])
            ->assertStatus(422);

        $this->assertEquals(Subject::GROUP_MODE_SELECT, $subject->fresh()->group_mode);
    }

    public function test_group_mode_can_change_when_no_groups_and_not_locked(): void
    {
        [, $classRoom, $subject] = $this->createSchool();

        $komting = $subject->classRoom->komting;
        $this->actingAs($komting)
            ->put("/class-rooms/{$classRoom->id}/subjects/{$subject->id}", [
                'name' => $subject->name,
                'group_mode' => Subject::GROUP_MODE_RANDOM,
            ])
            ->assertRedirect();

        $this->assertEquals(Subject::GROUP_MODE_RANDOM, $subject->fresh()->group_mode);
    }

    public function test_group_assignment_submission_marks_all_group_members_done(): void
    {
        Storage::fake('submissions');

        [, $classRoom, $subject] = $this->createSchool();
        $a = $this->enrollStudent($subject);
        $b = $this->enrollStudent($subject);
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Alpha']);
        $group->members()->attach([$a->id, $b->id]);

        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Tugas Kelompok',
            'type' => Assignment::TYPE_GROUP,
            'submission_mode' => Assignment::SUBMISSION_MODE_LINK,
        ]);
        $assignment->users()->attach([$a->id => ['status' => Assignment::STATUS_PENDING], $b->id => ['status' => Assignment::STATUS_PENDING]]);

        $this->actingAs($a)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'submission_url' => 'https://example.com/g',
            ])
            ->assertRedirect();

        $this->assertEquals(Assignment::STATUS_DONE, $assignment->users()->whereKey($a->id)->first()->pivot->status);
        $this->assertEquals(Assignment::STATUS_DONE, $assignment->users()->whereKey($b->id)->first()->pivot->status);
    }

    public function test_submission_blocked_after_graded(): void
    {
        Storage::fake('submissions');

        [, $classRoom, $subject] = $this->createSchool();
        $student = $this->enrollStudent($subject);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'T1',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 2,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_GRADED]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [UploadedFile::fake()->create('a.pdf', 50)],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('assignment_submissions', 0);
    }

    public function test_max_files_is_enforced_as_total(): void
    {
        Storage::fake('submissions');

        [, $classRoom, $subject] = $this->createSchool();
        $student = $this->enrollStudent($subject);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'T1',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 2,
            'allowed_extensions' => 'pdf',
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);
        $assignment->submissions()->create([
            'user_id' => $student->id,
            'original_name' => 'x.pdf',
            'path' => 'p/x.pdf',
            'size' => 1,
        ]);

        $this->actingAs($student)
            ->post("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submit", [
                'files' => [UploadedFile::fake()->create('a.pdf', 50), UploadedFile::fake()->create('b.pdf', 50)],
            ])
            ->assertSessionHasErrors('files');

        $this->assertSame(1, $assignment->fresh()->submissions()->count());
    }

    public function test_delete_file_resets_status_to_pending(): void
    {
        Storage::fake('submissions');

        [, $classRoom, $subject] = $this->createSchool();
        $student = $this->enrollStudent($subject);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'T1',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 2,
        ]);
        $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_DONE, 'submitted_at' => now()]);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'a.pdf',
            'path' => 'assignments/a.pdf',
            'size' => 50,
        ]);
        Storage::disk('submissions')->put($submission->path, 'x');

        $this->actingAs($student)
            ->delete("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submissions/{$submission->id}")
            ->assertRedirect();

        $pivot = $assignment->users()->whereKey($student->id)->first()->pivot;
        $this->assertEquals(Assignment::STATUS_PENDING, $pivot->status);
        $this->assertNull($pivot->submitted_at);

        Storage::disk('submissions')->assertMissing($submission->path);
        $this->assertDatabaseMissing('assignment_submissions', ['id' => $submission->id]);
    }

    public function test_delete_link_resets_status_when_no_file_left(): void
    {
        [, $classRoom, $subject] = $this->createSchool();
        $student = $this->enrollStudent($subject);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'T1',
            'submission_mode' => Assignment::SUBMISSION_MODE_LINK,
        ]);
        $assignment->users()->attach($student->id, [
            'status' => Assignment::STATUS_DONE,
            'submitted_at' => now(),
            'submission_url' => 'https://example.com',
        ]);

        $this->actingAs($student)
            ->delete("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submissions/link")
            ->assertRedirect();

        $pivot = $assignment->users()->whereKey($student->id)->first()->pivot;
        $this->assertEquals(Assignment::STATUS_PENDING, $pivot->status);
        $this->assertNull($pivot->submitted_at);
        $this->assertNull($pivot->submission_url);
    }

    public function test_non_member_cannot_delete_others_submission(): void
    {
        Storage::fake('submissions');

        [, $classRoom, $subject] = $this->createSchool();
        $owner = $this->enrollStudent($subject);
        $other = $this->enrollStudent($subject);
        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'T1',
            'submission_mode' => Assignment::SUBMISSION_MODE_FILE,
            'max_files' => 2,
        ]);
        $assignment->users()->attach([$owner->id => ['status' => Assignment::STATUS_DONE], $other->id => ['status' => Assignment::STATUS_PENDING]]);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $owner->id,
            'original_name' => 'a.pdf',
            'path' => 'assignments/a.pdf',
            'size' => 50,
        ]);

        $this->actingAs($other)
            ->delete("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/assignments/{$assignment->id}/submissions/{$submission->id}")
            ->assertForbidden();

        $this->assertNotNull($submission->fresh());
    }

    public function test_subject_remove_member_cleans_assignment_but_keeps_other_student(): void
    {
        Storage::fake('submissions');

        [, $classRoom, $subject] = $this->createSchool();
        $a = $this->enrollStudent($subject);
        $b = $this->enrollStudent($subject);
        $assignment = Assignment::create(['subject_id' => $subject->id, 'title' => 'T1']);
        $assignment->users()->attach([$a->id => ['status' => Assignment::STATUS_DONE], $b->id => ['status' => Assignment::STATUS_PENDING]]);

        $komting = $subject->classRoom->komting;
        $this->actingAs($komting)
            ->delete("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/members/{$a->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('assignment_user', ['user_id' => $a->id]);
        $this->assertDatabaseHas('assignment_user', ['user_id' => $b->id]);
    }
}
