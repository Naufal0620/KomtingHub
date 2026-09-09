<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\ShuffleRun;
use App\Models\Subject;
use App\Models\User;
use App\Services\GroupShuffleService;
use Illuminate\Http\Request;

class ShuffleController extends Controller
{
    public function create(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);
        abort_unless($subject->isRandomMode(), 403, 'Mata pelajaran ini tidak menggunakan pengacakan otomatis.');

        return view('shuffle.create', compact('classRoom', 'subject'));
    }

    public function store(Request $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);
        abort_unless($subject->isRandomMode(), 403, 'Mata pelajaran ini tidak menggunakan pengacakan otomatis.');

        $validated = $request->validate([
            'group_count' => ['nullable', 'integer', 'min:1', 'max:100', 'required_without:members_per_group'],
            'members_per_group' => ['nullable', 'integer', 'min:1', 'max:50', 'required_without:group_count'],
            'seed' => ['nullable', 'string', 'max:255'],
        ]);

        $service = app(GroupShuffleService::class);

        try {
            $run = $service->shuffle(
                $subject,
                groupCount: $validated['group_count'] ?? null,
                membersPerGroup: $validated['members_per_group'] ?? null,
                seed: $validated['seed'] ?? null,
                actor: $request->user(),
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['shuffle' => $e->getMessage()]);
        }

        return redirect()
            ->route('class-rooms.subjects.shuffle.show', [$classRoom, $subject, $run])
            ->with('success', 'Kelompok berhasil diacak.');
    }

    public function show(ClassRoom $classRoom, Subject $subject, ShuffleRun $run)
    {
        abort_unless($run->subject_id === $subject->id, 404);
        $this->authorize('view', $subject);

        $run->load('subject');
        $nameMap = $this->nameMapFromSnapshot($run);

        return view('shuffle.show', compact('classRoom', 'subject', 'run', 'nameMap'));
    }

    public function verify(Request $request, ShuffleRun $run)
    {
        $service = app(GroupShuffleService::class);
        $valid = $service->verify($run);
        $memberSetChanged = $service->memberSetChanged($run);

        $subject = $run->subject;
        $classRoom = $subject->classRoom;

        $result = $run->result ?? [];
        $position = 0;

        // Public page: never expose real names, only anonymous labels.
        foreach ($result as $index => $groupResult) {
            $result[$index]['members'] = collect($groupResult['members'] ?? [])
                ->map(function () use (&$position) {
                    $position++;

                    return 'Anggota #'.$position;
                })
                ->all();
        }

        return view('shuffle.verify', compact('result', 'run', 'subject', 'classRoom', 'valid', 'memberSetChanged'));
    }

    /**
     * Resolve member ids from the run's snapshot (falling back to the current
     * members for legacy runs) to a display name map.
     *
     * @return array<int, string>
     */
    private function nameMapFromSnapshot(ShuffleRun $run): array
    {
        $ids = $run->member_snapshot;
        $ids ??= $run->subject->members()->pluck('users.id')->all();

        if (empty($ids)) {
            return [];
        }

        return User::whereIn('id', $ids)->pluck('name', 'id')->all();
    }
}
