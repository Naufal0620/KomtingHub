<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShuffleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public randomization verification page (Phase 8)
Route::get('/verify/{run}', [ShuffleController::class, 'verify'])->name('shuffle.verify');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Admin-only: create class rooms and assign them to a komting
    Route::prefix('class-rooms')
        ->middleware('role:admin')
        ->name('class-rooms.')
        ->group(function () {
            Route::get('/create', [ClassRoomController::class, 'create'])->name('create');
            Route::post('/', [ClassRoomController::class, 'store'])->name('store');
            Route::get('/{classRoom}/edit', [ClassRoomController::class, 'edit'])->name('edit');
            Route::put('/{classRoom}', [ClassRoomController::class, 'update'])->name('update');
            Route::delete('/{classRoom}', [ClassRoomController::class, 'destroy'])->name('destroy');
        });

    // Admin-only: manage komting and student accounts
    Route::prefix('users')
        ->middleware('role:admin')
        ->name('users.')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

    // Komting (and admin) manage the contents of their assigned class rooms
    Route::prefix('class-rooms')
        ->middleware('role:komting')
        ->name('class-rooms.')
        ->group(function () {
            Route::get('/', [ClassRoomController::class, 'index'])->name('index');

            Route::get('/{classRoom}/members', [ClassRoomController::class, 'manageMembers'])->name('members.index');
            Route::post('/{classRoom}/members', [ClassRoomController::class, 'addMember'])->name('members.store');
            Route::delete('/{classRoom}/members/{user}', [ClassRoomController::class, 'removeMember'])->name('members.destroy');

            // Subjects nested under class rooms
            Route::get('/{classRoom}/subjects', [SubjectController::class, 'index'])->name('subjects.index');
            Route::get('/{classRoom}/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
            Route::post('/{classRoom}/subjects', [SubjectController::class, 'store'])->name('subjects.store');
            Route::get('/{classRoom}/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
            Route::put('/{classRoom}/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
            Route::delete('/{classRoom}/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

            Route::get('/{classRoom}/subjects/{subject}/members', [SubjectController::class, 'manageMembers'])->name('subjects.members.index');
            Route::post('/{classRoom}/subjects/{subject}/members', [SubjectController::class, 'addMember'])->name('subjects.members.store');
            Route::delete('/{classRoom}/subjects/{subject}/members/{user}', [SubjectController::class, 'removeMember'])->name('subjects.members.destroy');

            // Groups nested under subjects (komting management)
            Route::get('/{classRoom}/subjects/{subject}/groups/create', [GroupController::class, 'create'])->name('subjects.groups.create');
            Route::post('/{classRoom}/subjects/{subject}/groups', [GroupController::class, 'store'])->name('subjects.groups.store');
            Route::get('/{classRoom}/subjects/{subject}/groups/{group}/edit', [GroupController::class, 'edit'])->name('subjects.groups.edit');
            Route::put('/{classRoom}/subjects/{subject}/groups/{group}', [GroupController::class, 'update'])->name('subjects.groups.update');
            Route::delete('/{classRoom}/subjects/{subject}/groups/{group}', [GroupController::class, 'destroy'])->name('subjects.groups.destroy');

            Route::post('/{classRoom}/subjects/{subject}/groups/{group}/members', [GroupController::class, 'addMember'])->name('subjects.groups.members.store');
            Route::delete('/{classRoom}/subjects/{subject}/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('subjects.groups.members.destroy');

            Route::post('/{classRoom}/subjects/{subject}/groups-lock', [GroupController::class, 'lock'])->name('subjects.groups.lock');
            Route::post('/{classRoom}/subjects/{subject}/groups-unlock', [GroupController::class, 'unlock'])->name('subjects.groups.unlock');

            // Randomization (Phase 6 & 7)
            Route::get('/{classRoom}/subjects/{subject}/shuffle', [ShuffleController::class, 'create'])->name('subjects.shuffle.create');
            Route::post('/{classRoom}/subjects/{subject}/shuffle', [ShuffleController::class, 'store'])->name('subjects.shuffle.store');
            Route::get('/{classRoom}/subjects/{subject}/shuffle/{run}', [ShuffleController::class, 'show'])->name('subjects.shuffle.show');

            // Assignments (Phase 9 & 10)
            Route::get('/{classRoom}/subjects/{subject}/assignments/create', [AssignmentController::class, 'create'])->name('subjects.assignments.create');
            Route::post('/{classRoom}/subjects/{subject}/assignments', [AssignmentController::class, 'store'])->name('subjects.assignments.store');
            Route::get('/{classRoom}/subjects/{subject}/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('subjects.assignments.edit');
            Route::put('/{classRoom}/subjects/{subject}/assignments/{assignment}', [AssignmentController::class, 'update'])->name('subjects.assignments.update');
            Route::delete('/{classRoom}/subjects/{subject}/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('subjects.assignments.destroy');
            Route::post('/{classRoom}/subjects/{subject}/assignments/{assignment}/grade/{user}', [AssignmentController::class, 'grade'])->middleware('throttle:mutations')->name('subjects.assignments.grade');

            // Exports (Phase 11)
            Route::get('/{classRoom}/subjects/{subject}/export/groups', [ExportController::class, 'groups'])->name('subjects.export.groups');
            Route::get('/{classRoom}/subjects/{subject}/export/assignments', [ExportController::class, 'assignments'])->name('subjects.export.assignments');
        });

    // Notifications (Phase 12)
    Route::get('/notifications', NotificationController::class)->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Student-facing group actions (self-selection)
    Route::post('/class-rooms/{classRoom}/subjects/{subject}/groups/{group}/join', [GroupController::class, 'join'])->middleware('throttle:mutations')->name('class-rooms.subjects.groups.join');
    Route::delete('/class-rooms/{classRoom}/subjects/{subject}/groups/{group}/leave', [GroupController::class, 'leave'])->middleware('throttle:mutations')->name('class-rooms.subjects.groups.leave');

    // Student-facing assignment submission
    Route::post('/class-rooms/{classRoom}/subjects/{subject}/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->middleware('throttle:mutations')->name('class-rooms.subjects.assignments.submit');
    Route::get('/class-rooms/{classRoom}/subjects/{subject}/assignments/{assignment}/submissions/{submission}/download', [AssignmentController::class, 'download'])->name('class-rooms.subjects.assignments.submissions.download');
    Route::delete('/class-rooms/{classRoom}/subjects/{subject}/assignments/{assignment}/submissions/link/{user?}', [AssignmentController::class, 'deleteLink'])->middleware('throttle:mutations')->name('class-rooms.subjects.assignments.submissions.link.destroy');
    Route::delete('/class-rooms/{classRoom}/subjects/{subject}/assignments/{assignment}/submissions/{submission}', [AssignmentController::class, 'deleteSubmission'])->middleware('throttle:mutations')->name('class-rooms.subjects.assignments.submissions.destroy');

    // Read-only browsing for komting AND enrolled students (gated by policy `view`)
    Route::prefix('class-rooms')->name('class-rooms.')->group(function () {
        Route::get('/{classRoom}', [ClassRoomController::class, 'show'])->name('show');
        Route::get('/{classRoom}/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
        Route::get('/{classRoom}/subjects/{subject}/groups', [GroupController::class, 'index'])->name('subjects.groups.index');
        Route::get('/{classRoom}/subjects/{subject}/groups/{group}', [GroupController::class, 'show'])->name('subjects.groups.show');
        Route::get('/{classRoom}/subjects/{subject}/assignments', [AssignmentController::class, 'index'])->name('subjects.assignments.index');
        Route::get('/{classRoom}/subjects/{subject}/assignments/{assignment}', [AssignmentController::class, 'show'])->name('subjects.assignments.show');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
