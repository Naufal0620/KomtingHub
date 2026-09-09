<?php

namespace Tests\Feature;

use App\Exports\AssignmentExport;
use App\Exports\GroupExport;
use App\Models\Assignment;
use App\Models\ClassRoom;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: ClassRoom, 2: Subject, 3: User, 4: User}
     */
    private function makeFixture(): array
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create([
            'class_room_id' => $classRoom->id,
            'group_mode' => Subject::GROUP_MODE_SELECT,
        ]);
        [$studentA, $studentB] = User::factory()->count(2)->student()->create();
        $subject->members()->attach([$studentA->id, $studentB->id]);

        return [$komting, $classRoom, $subject, $studentA, $studentB];
    }

    public function test_owning_komting_can_export_groups_and_assignments(): void
    {
        [$komting, $classRoom, $subject] = $this->makeFixture();

        $groups = $this->actingAs($komting)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/export/groups");

        $groups->assertOk();
        $this->assertTrue($groups->headers->has('content-disposition'));

        $assignments = $this->actingAs($komting)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/export/assignments");

        $assignments->assertOk();
        $this->assertTrue($assignments->headers->has('content-disposition'));
    }

    public function test_student_cannot_export(): void
    {
        [$komting, $classRoom, $subject, $student] = $this->makeFixture();

        $this->actingAs($student)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/export/groups")
            ->assertForbidden();
    }

    public function test_non_owning_komting_cannot_export(): void
    {
        [$komting, $classRoom, $subject] = $this->makeFixture();
        $otherKomting = User::factory()->komting()->create();

        $this->actingAs($otherKomting)
            ->get("/class-rooms/{$classRoom->id}/subjects/{$subject->id}/export/groups")
            ->assertForbidden();
    }

    public function test_cross_subject_mismatch_returns_404(): void
    {
        [$komting, $classRoom, $subject] = $this->makeFixture();
        $otherClassRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);

        $this->actingAs($komting)
            ->get("/class-rooms/{$otherClassRoom->id}/subjects/{$subject->id}/export/groups")
            ->assertNotFound();
    }

    public function test_group_export_lists_all_members_incl_unassigned(): void
    {
        [$komting, $classRoom, $subject, $studentA, $studentB] = $this->makeFixture();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        $group->members()->attach($studentA->id);

        $rows = (new GroupExport($subject))->query()->get();

        $this->assertCount(2, $rows);
        $this->assertSame('Group Alpha', $rows->firstWhere('student_email', $studentA->email)->group_name);
        $this->assertNull($rows->firstWhere('student_email', $studentB->email)->group_name);
    }

    public function test_assignment_export_includes_group_column_and_status(): void
    {
        [$komting, $classRoom, $subject, $studentA, $studentB] = $this->makeFixture();
        $group = Group::create(['subject_id' => $subject->id, 'name' => 'Group Alpha']);
        $group->members()->attach($studentA->id);

        $assignment = Assignment::create([
            'subject_id' => $subject->id,
            'title' => 'Proyek Kelompok',
            'type' => Assignment::TYPE_GROUP,
        ]);
        $assignment->users()->attach($studentA->id, ['status' => Assignment::STATUS_GRADED, 'grade' => 90]);
        $assignment->users()->attach($studentB->id, ['status' => Assignment::STATUS_PENDING]);

        $export = new AssignmentExport($subject);
        $rows = $export->query()->get();

        $this->assertCount(2, $rows);

        $mappedA = $export->map($rows->firstWhere('student_email', $studentA->email));
        $this->assertSame('Group Alpha', $mappedA[6]);
        $this->assertSame('Dinilai', $mappedA[7]);

        $mappedB = $export->map($rows->firstWhere('student_email', $studentB->email));
        $this->assertSame('Belum di kelompok', $mappedB[6]);
        $this->assertSame('Tertunda', $mappedB[7]);
    }
}