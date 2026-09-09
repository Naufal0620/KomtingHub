<?php

namespace Tests\Unit;

use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use App\Services\GroupShuffleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RandomizationTest extends TestCase
{
    use RefreshDatabase;

    private function service(): GroupShuffleService
    {
        return app(GroupShuffleService::class);
    }

    private function subjectWithMembers(int $count): Subject
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create([
            'class_room_id' => $classRoom->id,
            'group_mode' => Subject::GROUP_MODE_RANDOM,
        ]);
        $students = User::factory()->count($count)->student()->create();
        $subject->members()->attach($students->pluck('id'));

        return $subject;
    }

    public function test_shuffle_is_deterministic_with_same_seed(): void
    {
        $subject = $this->subjectWithMembers(12);

        $run1 = $this->service()->shuffle($subject, groupCount: 4, seed: 'fixed-seed');
        $run2 = $this->service()->shuffle($subject, groupCount: 4, seed: 'fixed-seed');

        $this->assertEquals($run1->result, $run2->result);
    }

    public function test_shuffle_differs_with_different_seeds(): void
    {
        $subject = $this->subjectWithMembers(12);

        $run1 = $this->service()->shuffle($subject, groupCount: 4, seed: 'seed-a');
        $run2 = $this->service()->shuffle($subject, groupCount: 4, seed: 'seed-b');

        $this->assertNotEquals($run1->result, $run2->result);
    }

    public function test_shuffle_records_verifiable_hash(): void
    {
        $subject = $this->subjectWithMembers(12);

        $run = $this->service()->shuffle($subject, groupCount: 3, seed: 'audit-seed');

        $this->assertTrue($this->service()->verify($run));
        $this->assertSame(3, $run->group_count);
        $this->assertCount(3, $run->result);
    }

    public function test_shuffle_distributes_all_members(): void
    {
        $subject = $this->subjectWithMembers(10);

        $run = $this->service()->shuffle($subject, groupCount: 5, seed: 'dist');

        $memberIds = collect($run->result)->flatMap(fn ($r) => $r['members']);
        $this->assertCount(10, $memberIds);
        $this->assertEquals(10, $memberIds->unique()->count());
    }

    public function test_members_per_group_mode(): void
    {
        $subject = $this->subjectWithMembers(12);

        $run = $this->service()->shuffle($subject, membersPerGroup: 3, seed: 'mpg');

        $this->assertSame(4, $run->group_count);
    }

    public function test_verify_is_stable_when_members_change_after_run(): void
    {
        $subject = $this->subjectWithMembers(12);

        $run = $this->service()->shuffle($subject, groupCount: 4, seed: 'stable-seed');

        $this->assertTrue($this->service()->verify($run));

        $newStudent = User::factory()->student()->create();
        $subject->members()->attach($newStudent->id);

        $this->assertTrue($this->service()->verify($run));
        $this->assertTrue($this->service()->memberSetChanged($run));
    }

    public function test_run_records_members_per_group_and_snapshot(): void
    {
        $subject = $this->subjectWithMembers(12);

        $run = $this->service()->shuffle($subject, membersPerGroup: 3, seed: 'mpg');

        $this->assertSame(3, $run->members_per_group);
        $this->assertCount(12, $run->member_snapshot);
        $this->assertSame(12, collect($run->result)->sum(fn ($r) => count($r['members'])));
    }

    public function test_shuffle_rejects_excessive_group_count(): void
    {
        $subject = $this->subjectWithMembers(5);

        $this->expectException(\RuntimeException::class);
        $this->service()->shuffle($subject, groupCount: 1000, seed: 'x');
    }

    public function test_shuffle_rejects_reallocation_expectations(): void
    {
        $subject = $this->subjectWithMembers(10);

        $this->expectException(\RuntimeException::class);
        $this->service()->shuffle($subject, groupCount: 11, seed: 'x');
    }

    public function test_shuffle_rejects_locked_subject(): void
    {
        $subject = $this->subjectWithMembers(12);
        $subject->update(['groups_locked' => true]);

        $this->expectException(\RuntimeException::class);
        $this->service()->shuffle($subject, groupCount: 4, seed: 'x');
    }

    public function test_shuffle_requires_members(): void
    {
        $komting = User::factory()->komting()->create();
        $classRoom = ClassRoom::factory()->create(['komting_id' => $komting->id]);
        $subject = Subject::factory()->create([
            'class_room_id' => $classRoom->id,
            'group_mode' => Subject::GROUP_MODE_RANDOM,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->service()->shuffle($subject, groupCount: 4, seed: 'x');
    }
}
