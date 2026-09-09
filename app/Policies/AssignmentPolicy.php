<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        $subject = $assignment->subject;

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isKomting() && $subject->classRoom->komting_id === $user->id) {
            return true;
        }

        return $user->subjects()->whereKey($subject->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isKomtingOrAdmin();
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $assignment->subject->classRoom->komting_id === $user->id);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $assignment->subject->classRoom->komting_id === $user->id);
    }

    public function manage(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $assignment->subject->classRoom->komting_id === $user->id);
    }

    public function submit(User $user, Assignment $assignment): bool
    {
        if (! $user->isStudent()) {
            return false;
        }

        return $user->subjects()->whereKey($assignment->subject_id)->exists();
    }

    public function download(User $user, AssignmentSubmission $submission): bool
    {
        if ($user->id === $submission->user_id) {
            return true;
        }

        return $this->manage($user, $submission->assignment);
    }

    public function deleteSubmission(User $user, AssignmentSubmission $submission): bool
    {
        return $this->download($user, $submission);
    }

    public function deleteLink(User $user, Assignment $assignment, User $target): bool
    {
        if ($user->id === $target->id) {
            return true;
        }

        return $this->manage($user, $assignment);
    }
}
