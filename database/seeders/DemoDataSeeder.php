<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\ClassRoom;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use App\Services\GroupShuffleService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Keep the seeder idempotent: drop any previously-seeded demo class so
        // re-running `db:seed` does not hit unique-constraint violations.
        ClassRoom::where('code', 'IF-24')->delete();

        $komting = User::updateOrCreate(
            ['email' => 'komting@example.com'],
            [
                'name' => 'Demo Komting',
                'role' => User::ROLE_KOMTING,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $classRoom = ClassRoom::create([
            'komting_id' => $komting->id,
            'name' => 'Informatics 2024',
            'code' => 'IF-24',
            'description' => 'Demo class for the KomtingHub system.',
        ]);

        $students = User::factory()->count(20)->student()->create();
        $classRoom->members()->attach($students->pluck('id'));

        $this->seedSelectSubject($classRoom, $students);
        $this->seedRandomSubject($classRoom, $students);
        $this->seedAssignmentsSubject($classRoom, $students);
    }

    private function seedSelectSubject(ClassRoom $classRoom, $students): void
    {
        $subject = Subject::create([
            'class_room_id' => $classRoom->id,
            'name' => 'Software Engineering',
            'code' => 'SE',
            'description' => 'Group project with self-selected teams.',
            'group_mode' => Subject::GROUP_MODE_SELECT,
        ]);
        $subject->members()->attach($students->pluck('id'));

        $groupA = Group::create(['subject_id' => $subject->id, 'name' => 'Team Alpha']);
        $groupB = Group::create(['subject_id' => $subject->id, 'name' => 'Team Beta']);
        $groupC = Group::create(['subject_id' => $subject->id, 'name' => 'Team Gamma']);

        $students->take(8)->each(fn ($s) => $groupA->members()->attach($s->id));
        $students->slice(8, 7)->each(fn ($s) => $groupB->members()->attach($s->id));
        $students->skip(15)->each(fn ($s) => $groupC->members()->attach($s->id));

        $assignment = $subject->assignments()->create([
            'title' => 'Group Project Proposal',
            'description' => 'Outline your team project plan.',
            'due_date' => now()->addWeeks(2),
            'type' => Assignment::TYPE_GROUP,
        ]);
        $students->each(fn ($s) => $assignment->users()->attach($s->id, ['status' => Assignment::STATUS_PENDING]));
    }

    private function seedRandomSubject(ClassRoom $classRoom, $students): void
    {
        $subject = Subject::create([
            'class_room_id' => $classRoom->id,
            'name' => 'Data Structures',
            'code' => 'DS',
            'description' => 'Lab teams assigned by a transparent random shuffle.',
            'group_mode' => Subject::GROUP_MODE_RANDOM,
        ]);
        $subject->members()->attach($students->pluck('id'));

        app(GroupShuffleService::class)->shuffle($subject, groupCount: 4, seed: 'demo-seed-2024');

        $subject->update(['groups_locked' => true]);
    }

    private function seedAssignmentsSubject(ClassRoom $classRoom, $students): void
    {
        $subject = Subject::create([
            'class_room_id' => $classRoom->id,
            'name' => 'Web Programming',
            'code' => 'WP',
            'description' => 'Individual assignments tracked per member.',
            'group_mode' => Subject::GROUP_MODE_SELECT,
        ]);
        $subject->members()->attach($students->pluck('id'));

        $this->createAssignmentWithProgress($subject, $students, 'HTML/CSS Basics', now()->addWeek());
        $this->createAssignmentWithProgress($subject, $students, 'Laravel Mini Project', now()->addMonth());
    }

    private function createAssignmentWithProgress(Subject $subject, $students, string $title, $due): void
    {
        $assignment = $subject->assignments()->create([
            'title' => $title,
            'description' => 'Complete and submit your work.',
            'due_date' => $due,
            'type' => Assignment::TYPE_INDIVIDUAL,
        ]);

        $students->each(function (User $student) use ($assignment) {
            $assignment->users()->attach($student->id, ['status' => Assignment::STATUS_PENDING]);
        });
    }
}
