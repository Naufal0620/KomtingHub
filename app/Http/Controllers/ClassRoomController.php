<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassRoomRequest;
use App\Http\Requests\UpdateClassRoomRequest;
use App\Models\ClassRoom;
use App\Models\User;
use App\Services\MemberCleanup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassRoomController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ClassRoom::class);

        $query = $request->user()->isAdmin()
            ? ClassRoom::query()
            : $request->user()->managedClassRooms();

        $classRooms = $query->with('komting')->withCount('members', 'subjects')
            ->latest()
            ->paginate(resolvePerPage($request, 10))
            ->withQueryString();

        return view('class-rooms.index', compact('classRooms'));
    }

    public function create()
    {
        $komtings = User::where('role', User::ROLE_KOMTING)
            ->orderBy('name')
            ->get();

        return view('class-rooms.create', compact('komtings'));
    }

    public function store(StoreClassRoomRequest $request)
    {
        ClassRoom::create($request->validated());

        return redirect()
            ->route('class-rooms.index')
            ->with('success', 'Ruang kelas berhasil dibuat.');
    }

    public function show(Request $request, ClassRoom $classRoom)
    {
        $this->authorize('view', $classRoom);

        $classRoom->load([
            'subjects' => fn ($q) => $q->withCount('groups'),
            'komting',
        ]);

        $totalGroups = $classRoom->subjects->sum('groups_count');

        $members = $classRoom->members()
            ->orderBy('name')
            ->paginate(resolvePerPage($request, 15))
            ->withQueryString();

        return view('class-rooms.show', compact('classRoom', 'members', 'totalGroups'));
    }

    public function edit(ClassRoom $classRoom)
    {
        $this->authorize('update', $classRoom);

        $komtings = User::where('role', User::ROLE_KOMTING)
            ->orderBy('name')
            ->get();

        return view('class-rooms.edit', compact('classRoom', 'komtings'));
    }

    public function update(UpdateClassRoomRequest $request, ClassRoom $classRoom)
    {
        $this->authorize('update', $classRoom);

        $classRoom->update($request->validated());

        return redirect()
            ->route('class-rooms.index')
            ->with('success', 'Ruang kelas berhasil diperbarui.');
    }

    public function destroy(ClassRoom $classRoom)
    {
        $this->authorize('delete', $classRoom);

        $classRoom->delete();

        return redirect()
            ->route('class-rooms.index')
            ->with('success', 'Ruang kelas berhasil dihapus.');
    }

    public function manageMembers(Request $request, ClassRoom $classRoom)
    {
        $this->authorize('manage', $classRoom);

        $members = $classRoom->members()
            ->orderBy('name')
            ->paginate(resolvePerPage($request, 15))
            ->withQueryString();

        $availableUsers = User::where('role', User::ROLE_STUDENT)
            ->whereDoesntHave('classRooms', fn ($q) => $q->where('class_rooms.id', $classRoom->id))
            ->orderBy('name')
            ->get();

        return view('class-rooms.members', compact('classRoom', 'members', 'availableUsers'));
    }

    public function addMember(ClassRoom $classRoom)
    {
        $this->authorize('manage', $classRoom);

        $userIds = request()->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ])['user_ids'];

        $validCount = User::whereIn('id', $userIds)
            ->where('role', User::ROLE_STUDENT)
            ->count();

        abort_unless($validCount === count($userIds), 422, 'Hanya pengguna mahasiswa yang dapat ditambahkan.');

        $classRoom->members()->syncWithoutDetaching($userIds);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function removeMember(ClassRoom $classRoom, User $user)
    {
        $this->authorize('manage', $classRoom);

        DB::transaction(function () use ($classRoom, $user) {
            MemberCleanup::removeFromSubjects($classRoom->subjects()->pluck('id'), $user->id);
            $classRoom->members()->detach($user);
        });

        return back()->with('success', 'Anggota berhasil dihapus.');
    }
}
