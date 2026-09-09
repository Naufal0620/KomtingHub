<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Mail\AssignmentCreatedMail;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\AssignmentCreatedNotification;
use App\Services\MemberCleanup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SubjectController extends Controller
{
    public function index(Request $request, ClassRoom $classRoom)
    {
        $this->authorize('view', $classRoom);

        $subjects = $classRoom->subjects()
            ->withCount('members', 'groups', 'assignments')
            ->orderBy('name')
            ->paginate(resolvePerPage($request, 12))
            ->withQueryString();

        return view('subjects.index', compact('classRoom', 'subjects'));
    }

    public function create(ClassRoom $classRoom)
    {
        $this->authorize('manage', $classRoom);

        return view('subjects.create', compact('classRoom'));
    }

    public function store(StoreSubjectRequest $request, ClassRoom $classRoom)
    {
        $this->authorize('manage', $classRoom);

        $classRoom->subjects()->create($request->validated());

        return redirect()
            ->route('class-rooms.subjects.index', $classRoom)
            ->with('success', 'Mata pelajaran berhasil dibuat.');
    }

    public function show(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('view', $subject);

        $subject->load([
            'members',
            'groups.members',
            'assignments',
        ]);

        return view('subjects.show', compact('classRoom', 'subject'));
    }

    public function edit(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('update', $subject);

        return view('subjects.edit', compact('classRoom', 'subject'));
    }

    public function update(UpdateSubjectRequest $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('update', $subject);

        $data = $request->validated();

        if (array_key_exists('group_mode', $data) && $data['group_mode'] !== $subject->group_mode) {
            abort_if(
                $subject->groups_locked || $subject->groups()->exists(),
                422,
                'Mode pembentukan kelompok tidak dapat diubah karena kelompok sudah terbentuk.'
            );
        }

        $subject->update($data);

        return redirect()
            ->route('class-rooms.subjects.index', $classRoom)
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('delete', $subject);

        $subject->delete();

        return redirect()
            ->route('class-rooms.subjects.index', $classRoom)
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    public function manageMembers(Request $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        $members = $subject->members()
            ->orderBy('name')
            ->paginate(resolvePerPage($request, 15))
            ->withQueryString();

        $availableUsers = $classRoom->members()
            ->whereDoesntHave('subjects', fn ($q) => $q->where('subjects.id', $subject->id))
            ->orderBy('name')
            ->get();

        return view('subjects.members', compact('classRoom', 'subject', 'members', 'availableUsers'));
    }

    public function addMember(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        $userIds = request()->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ])['user_ids'];

        $validCount = $classRoom->members()
            ->whereIn('users.id', $userIds)
            ->count();

        abort_unless($validCount === count($userIds), 422, 'Pengguna terpilih bukan anggota ruang kelas ini.');

        $existingMemberIds = $subject->members()->pluck('users.id');
        $newUserIds = collect($userIds)->reject(fn ($id) => $existingMemberIds->contains($id));

        $subject->members()->syncWithoutDetaching($userIds);

        // Backfill: notify newly enrolled members about already-created assignments.
        if ($newUserIds->isNotEmpty()) {
            $users = User::whereIn('id', $newUserIds)->get();
            $assignments = $subject->assignments()->get();

            foreach ($users as $user) {
                foreach ($assignments as $assignment) {
                    $user->notify(new AssignmentCreatedNotification($assignment));
                    Mail::to($user)->queue(new AssignmentCreatedMail($assignment));
                }
            }
        }

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function removeMember(ClassRoom $classRoom, Subject $subject, User $user)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        DB::transaction(function () use ($subject, $user) {
            MemberCleanup::removeFromSubjects([$subject->id], $user->id);
            $subject->members()->detach($user);
        });

        return back()->with('success', 'Anggota berhasil dihapus.');
    }
}
