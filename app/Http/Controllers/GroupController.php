<?php

namespace App\Http\Controllers;

use App\Events\GroupLocked;
use App\Events\GroupUnlocked;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\ClassRoom;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function index(Request $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('view', $subject);

        $groups = $subject->groups()
            ->with('members')
            ->orderBy('name')
            ->paginate(resolvePerPage($request, 9))
            ->withQueryString();

        return view('groups.index', compact('classRoom', 'subject', 'groups'));
    }

    public function create(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        return view('groups.create', compact('classRoom', 'subject'));
    }

    public function store(StoreGroupRequest $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);
        abort_if($subject->groups_locked, 403, 'Kelompok sudah dikunci.');

        $subject->groups()->create($request->validated());

        return redirect()
            ->route('class-rooms.subjects.groups.index', [$classRoom, $subject])
            ->with('success', 'Kelompok berhasil dibuat.');
    }

    public function show(Request $request, ClassRoom $classRoom, Subject $subject, Group $group)
    {
        abort_unless($group->subject_id === $subject->id, 404);
        $this->authorize('view', $group);

        $members = $group->members()
            ->orderBy('name')
            ->paginate(resolvePerPage($request, 15))
            ->withQueryString();

        $availableMembers = $subject->members()
            ->whereDoesntHave('groups', fn ($q) => $q->where('groups.id', $group->id))
            ->orderBy('name')
            ->get();

        return view('groups.show', compact('classRoom', 'subject', 'group', 'members', 'availableMembers'));
    }

    public function addMember(Request $request, ClassRoom $classRoom, Subject $subject, Group $group)
    {
        abort_unless($group->subject_id === $subject->id, 404);
        $this->authorize('manage', $group);

        $userIds = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ])['user_ids'];

        $validCount = $subject->members()
            ->whereIn('users.id', $userIds)
            ->count();

        abort_unless($validCount === count($userIds), 422, 'Pengguna terpilih bukan anggota mata pelajaran ini.');

        $group->members()->syncWithoutDetaching($userIds);

        return back()->with('success', 'Anggota berhasil ditambahkan ke kelompok.');
    }

    public function removeMember(ClassRoom $classRoom, Subject $subject, Group $group, User $user)
    {
        abort_unless($group->subject_id === $subject->id, 404);
        $this->authorize('manage', $group);

        $group->members()->detach($user);

        return back()->with('success', 'Anggota berhasil dihapus dari kelompok.');
    }

    public function edit(ClassRoom $classRoom, Subject $subject, Group $group)
    {
        abort_unless($group->subject_id === $subject->id, 404);
        $this->authorize('update', $group);

        return view('groups.edit', compact('classRoom', 'subject', 'group'));
    }

    public function update(UpdateGroupRequest $request, ClassRoom $classRoom, Subject $subject, Group $group)
    {
        abort_unless($group->subject_id === $subject->id, 404);
        $this->authorize('update', $group);
        abort_if($subject->groups_locked, 403, 'Kelompok sudah dikunci.');

        $group->update($request->validated());

        return redirect()
            ->route('class-rooms.subjects.groups.index', [$classRoom, $subject])
            ->with('success', 'Kelompok berhasil diperbarui.');
    }

    public function destroy(ClassRoom $classRoom, Subject $subject, Group $group)
    {
        abort_unless($group->subject_id === $subject->id, 404);
        $this->authorize('delete', $group);
        abort_if($subject->groups_locked, 403, 'Kelompok sudah dikunci.');

        $group->delete();

        return redirect()
            ->route('class-rooms.subjects.groups.index', [$classRoom, $subject])
            ->with('success', 'Kelompok berhasil dihapus.');
    }

    public function join(ClassRoom $classRoom, Subject $subject, Group $group)
    {
        abort_unless($group->subject_id === $subject->id, 404);
        abort_if($subject->groups_locked, 403, 'Kelompok sudah dikunci.');

        $subject->mustAllowSelfSelection();

        $user = request()->user();
        abort_unless($user->isStudent(), 403, 'Hanya mahasiswa yang dapat memilih kelompok.');
        abort_unless($subject->members()->whereKey($user->id)->exists(), 403, 'Kamu tidak terdaftar pada mata pelajaran ini.');

        DB::transaction(function () use ($user, $subject, $group) {
            $user->groups()->where('groups.subject_id', $subject->id)->detach();
            $group->members()->syncWithoutDetaching($user->id);
        });

        return back()->with('success', 'Kamu berhasil bergabung ke kelompok.');
    }

    public function leave(ClassRoom $classRoom, Subject $subject, Group $group)
    {
        abort_unless($group->subject_id === $subject->id, 404);
        abort_if($subject->groups_locked, 403, 'Kelompok sudah dikunci.');

        $subject->mustAllowSelfSelection();

        $user = request()->user();
        abort_unless($user->isStudent(), 403, 'Hanya mahasiswa yang dapat memilih kelompok.');
        abort_unless($group->members()->whereKey($user->id)->exists(), 403, 'Kamu tidak berada di kelompok ini.');

        $group->members()->detach($user);

        return back()->with('success', 'Kamu berhasil keluar dari kelompok.');
    }

    public function lock(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        DB::transaction(function () use ($subject) {
            $groupIds = $subject->groups()->pluck('id');
            DB::table('group_user')
                ->whereIn('group_id', $groupIds)
                ->update(['locked_at' => now()]);
            $subject->update(['groups_locked' => true]);
        });

        GroupLocked::dispatch($subject);

        return back()->with('success', 'Kelompok berhasil dikunci.');
    }

    public function unlock(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        $subject->update(['groups_locked' => false]);

        GroupUnlocked::dispatch($subject);

        return back()->with('success', 'Kelompok berhasil dibuka kuncinya.');
    }
}
