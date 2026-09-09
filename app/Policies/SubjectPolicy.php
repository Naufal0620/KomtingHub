<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isKomtingOrAdmin();
    }

    public function view(User $user, Subject $subject): bool
    {
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

    public function update(User $user, Subject $subject): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $subject->classRoom->komting_id === $user->id);
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $subject->classRoom->komting_id === $user->id);
    }

    public function manage(User $user, Subject $subject): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $subject->classRoom->komting_id === $user->id);
    }
}
