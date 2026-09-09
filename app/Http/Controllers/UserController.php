<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    private const MANAGED_ROLES = [User::ROLE_KOMTING, User::ROLE_STUDENT];

    public function index(Request $request)
    {
        $filter = $request->query('role');

        $users = User::where('role', '<>', User::ROLE_ADMIN)
            ->when(in_array($filter, self::MANAGED_ROLES, true), fn ($q) => $q->where('role', $filter))
            ->withCount('classRooms', 'subjects')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(resolvePerPage($request, 10))
            ->withQueryString();

        $counts = [
            'all' => User::where('role', '<>', User::ROLE_ADMIN)->count(),
            'komting' => User::where('role', User::ROLE_KOMTING)->count(),
            'student' => User::where('role', User::ROLE_STUDENT)->count(),
        ];

        return view('users.index', compact('users', 'filter', 'counts'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(self::MANAGED_ROLES)],
        ]);

        $user = new User($data);
        $user->email_verified_at = now();
        $user->save();

        return redirect()
            ->route('users.index', ['role' => $data['role']])
            ->with('success', 'Akun '.self::roleLabel($data['role']).' berhasil dibuat.');
    }

    public function edit(User $user)
    {
        abort_if($user->isAdmin(), 404, 'Akun admin tidak dapat dikelola di sini.');

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->isAdmin(), 404, 'Akun admin tidak dapat dikelola di sini.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(self::MANAGED_ROLES)],
        ]);

        if ($user->isKomting() && $data['role'] !== User::ROLE_KOMTING && $user->managedClassRooms()->exists()) {
            abort(422, 'Komting ini masih mengelola ruang kelas. Tugaskan ulang ruang kelasnya sebelum mengubah perannya.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index', ['role' => $data['role']])
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'Tidak dapat menghapus akun sendiri.');
        abort_if($user->isAdmin(), 422, 'Tidak dapat menghapus akun admin.');
        abort_if($user->managedClassRooms()->exists(), 422, 'Komting ini masih mengelola ruang kelas. Tugaskan ulang ruang kelasnya sebelum menghapus akun.');

        $user->delete();

        return redirect()
            ->route('users.index', ['role' => $request->query('role')])
            ->with('success', 'Akun berhasil dihapus.');
    }

    private static function roleLabel(string $role): string
    {
        return match ($role) {
            User::ROLE_KOMTING => 'komting',
            User::ROLE_STUDENT => 'mahasiswa',
            default => 'pengguna',
        };
    }
}