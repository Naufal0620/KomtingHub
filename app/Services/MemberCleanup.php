<?php

namespace App\Services;

use App\Models\AssignmentSubmission;
use App\Models\Group;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MemberCleanup
{
    /**
     * Remove every Subject-level membership of a user for the given subjects:
     * subject enrollments, group memberships, assignment progress rows, and
     * uploaded submission files are all cleaned up so no orphaned records remain.
     *
     * @param  iterable<int>  $subjectIds
     */
    public static function removeFromSubjects(iterable $subjectIds, int $userId): void
    {
        $subjectIds = Collection::make($subjectIds)->map(fn ($id) => (int) $id)->all();

        if ($subjectIds === []) {
            return;
        }

        $groupIds = Group::whereIn('subject_id', $subjectIds)->pluck('id');

        DB::table('group_user')
            ->whereIn('group_id', $groupIds)
            ->where('user_id', $userId)
            ->delete();

        DB::table('subject_user')
            ->whereIn('subject_id', $subjectIds)
            ->where('user_id', $userId)
            ->delete();

        $assignmentIds = DB::table('assignments')
            ->whereIn('subject_id', $subjectIds)
            ->pluck('id');

        DB::table('assignment_user')
            ->whereIn('assignment_id', $assignmentIds)
            ->where('user_id', $userId)
            ->delete();

        $paths = AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
            ->where('user_id', $userId)
            ->pluck('path');

        foreach ($paths as $path) {
            Storage::disk('submissions')->delete($path);
        }

        AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
            ->where('user_id', $userId)
            ->delete();
    }
}
