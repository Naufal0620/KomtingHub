<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->isKomting()) {
            return $this->komtingDashboard($user, $request);
        }

        if ($user->isAdmin()) {
            return $this->adminDashboard($user, $request);
        }

        return $this->studentDashboard($user);
    }

    private function studentDashboard(User $user)
    {
        $classRooms = $user->classRooms()
            ->withCount('subjects')
            ->orderBy('name')
            ->get();

        $subjects = $user->subjects()
            ->withCount('groups', 'assignments')
            ->orderBy('name')
            ->get();

        $groupCount = $user->groups()->count();

        return view('dashboard.student', compact('classRooms', 'subjects', 'groupCount'));
    }

    private function komtingDashboard(User $user, Request $request)
    {
        $classIds = $user->managedClassRooms()->pluck('id');

        $classRooms = $user->managedClassRooms()
            ->withCount('members', 'subjects')
            ->orderBy('name')
            ->paginate(resolvePerPage($request))
            ->withQueryString();

        $totalSubjects = Subject::whereIn('class_room_id', $classIds)->count();

        $totalMembers = DB::table('class_user')
            ->whereIn('class_room_id', $classIds)
            ->distinct()
            ->count('user_id');

        return view('dashboard.komting', compact('classRooms', 'totalSubjects', 'totalMembers'));
    }

    private function adminDashboard(User $user, Request $request)
    {
        $classRooms = ClassRoom::query()
            ->withCount('members', 'subjects')
            ->with('komting:id,name')
            ->latest()
            ->paginate(resolvePerPage($request))
            ->withQueryString();

        $totalSubjects = Subject::count();

        $totalMembers = DB::table('class_user')->distinct()->count('user_id');

        $totalKomting = User::where('role', User::ROLE_KOMTING)->count();

        return view('dashboard.admin', compact('classRooms', 'totalSubjects', 'totalMembers', 'totalKomting'));
    }
}