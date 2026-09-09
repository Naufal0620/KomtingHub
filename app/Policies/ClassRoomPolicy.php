<?php

namespace App\Policies;

use App\Models\ClassRoom;
use App\Models\User;

class ClassRoomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isKomtingOrAdmin();
    }

    public function view(User $user, ClassRoom $classRoom): bool
    {
        return $user->isKomtingOrAdmin() || $classRoom->members->contains($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ClassRoom $classRoom): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ClassRoom $classRoom): bool
    {
        return $user->isAdmin();
    }

    public function manage(User $user, ClassRoom $classRoom): bool
    {
        return $user->isAdmin() || ($user->isKomting() && $classRoom->komting_id === $user->id);
    }
}
