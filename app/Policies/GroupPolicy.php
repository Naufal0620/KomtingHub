<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function view(User $user, Group $group): bool
    {
        $subject = $group->subject;

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

    public function update(User $user, Group $group): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $group->subject->classRoom->komting_id === $user->id);
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $group->subject->classRoom->komting_id === $user->id);
    }

    public function manage(User $user, Group $group): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $group->subject->classRoom->komting_id === $user->id);
    }
}
