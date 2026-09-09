<?php

namespace App\Http\Controllers;

use App\Events\AssignmentCreated;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AssignmentController extends Controller
{
    public function index(Request $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('view', $subject);

        $assignments = $subject->assignments()
            ->with('users')
            ->latest()
            ->paginate(resolvePerPage($request, 10))
            ->withQueryString();

        return view('assignments.index', compact('classRoom', 'subject', 'assignments'));
    }

    public function create(ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        return view('assignments.create', compact('classRoom', 'subject'));
    }

    public function store(StoreAssignmentRequest $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        $assignment = $subject->assignments()->create(
            $this->normalizeSubmissionData($request->validated())
        );

        // Initialize progress rows for each enrolled member.
        $memberData = $subject->members->mapWithKeys(fn (User $user) => [
            $user->id => ['status' => Assignment::STATUS_PENDING],
        ]);

        $assignment->users()->attach($memberData);

        AssignmentCreated::dispatch($assignment);

        return redirect()
            ->route('class-rooms.subjects.assignments.index', [$classRoom, $subject])
            ->with('success', 'Tugas berhasil dibuat.');
    }

    public function show(Request $request, ClassRoom $classRoom, Subject $subject, Assignment $assignment)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);
        $this->authorize('view', $assignment);

        $assignment->load(['users', 'submissions.user']);

        $assignmentUsers = $assignment->users()
            ->paginate(resolvePerPage($request, 12))
            ->withQueryString();

        return view('assignments.show', compact('classRoom', 'subject', 'assignment', 'assignmentUsers'));
    }

    public function edit(ClassRoom $classRoom, Subject $subject, Assignment $assignment)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);
        $this->authorize('update', $assignment);

        return view('assignments.edit', compact('classRoom', 'subject', 'assignment'));
    }

    public function update(UpdateAssignmentRequest $request, ClassRoom $classRoom, Subject $subject, Assignment $assignment)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);
        $this->authorize('update', $assignment);

        $assignment->update($this->normalizeSubmissionData($request->validated()));

        return redirect()
            ->route('class-rooms.subjects.assignments.index', [$classRoom, $subject])
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(ClassRoom $classRoom, Subject $subject, Assignment $assignment)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);
        $this->authorize('delete', $assignment);

        $assignment->delete();

        return redirect()
            ->route('class-rooms.subjects.assignments.index', [$classRoom, $subject])
            ->with('success', 'Tugas berhasil dihapus.');
    }

    public function submit(Request $request, ClassRoom $classRoom, Subject $subject, Assignment $assignment)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);
        $this->authorize('submit', $assignment);

        $user = $request->user();

        $existing = $assignment->users()->whereKey($user->id)->first();
        if ($existing?->pivot->status === Assignment::STATUS_GRADED) {
            abort(403, 'Tugas ini sudah dinilai dan tidak dapat dikumpulkan lagi.');
        }

        $needsFile = $assignment->requiresFile();
        $needsLink = $assignment->requiresLink();

        $existingFileCount = $assignment->submissions()->where('user_id', $user->id)->count();
        $remaining = max(0, $assignment->max_files - $existingFileCount);

        $files = $request->file('files');

        $rules = [];
        $rules['submission_url'] = $needsLink ? ['required', 'url', 'max:2048'] : ['nullable'];
        if ($needsFile && $remaining > 0) {
            $rules['files'] = ['required', 'array', 'max:'.$remaining];
            $rules['files.*'] = ['file', 'mimes:'.implode(',', $assignment->allowedExtensionList()), 'max:'.$assignment->max_file_size_kb];
        } else {
            $rules['files'] = ['nullable', 'array'];
            $files = [];
        }

        $validated = $request->validate($rules);

        $isLate = $assignment->isLate(now());

        $storedPaths = [];

        try {
            DB::transaction(function () use ($user, $assignment, $subject, $files, $validated, $isLate, &$storedPaths) {
                foreach ($files as $file) {
                    $path = $file->store('assignments/'.$assignment->id.'/'.$user->id, 'submissions');
                    $storedPaths[] = $path;

                    $assignment->submissions()->create([
                        'user_id' => $user->id,
                        'original_name' => $this->sanitizeFileName($file->getClientOriginalName()),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                        'is_late' => $isLate,
                    ]);
                }

                $assignment->users()->syncWithoutDetaching([
                    $user->id => [
                        'status' => Assignment::STATUS_DONE,
                        'submitted_at' => now(),
                        'submission_url' => $validated['submission_url'] ?? null,
                    ],
                ]);

                if ($assignment->type === Assignment::TYPE_GROUP) {
                    $this->syncGroupSubmissionProgress($assignment, $subject, $user);
                }
            });
        } catch (Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('submissions')->delete($path);
            }
            throw $e;
        }

        return back()->with(
            'success',
            $isLate
                ? 'Tugas dikumpulkan setelah lewat tenggat (telat), tetapi tetap diterima.'
                : 'Tugas berhasil dikumpulkan.'
        );
    }

    public function download(ClassRoom $classRoom, Subject $subject, Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);
        abort_unless($submission->assignment_id === $assignment->id, 404);

        $this->authorize('download', $submission);

        if (! Storage::disk('submissions')->exists($submission->path)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        return Storage::disk('submissions')->download($submission->path, $submission->original_name);
    }

    public function deleteSubmission(ClassRoom $classRoom, Subject $subject, Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);
        abort_unless($submission->assignment_id === $assignment->id, 404);

        $this->authorize('deleteSubmission', $submission);

        $owner = $submission->user;

        Storage::disk('submissions')->delete($submission->path);
        $submission->delete();

        if ($owner) {
            $this->recomputePivotStatus($assignment, $owner);
        }

        return back()->with('success', 'Berkas pengumpulan berhasil dihapus.');
    }

    public function deleteLink(ClassRoom $classRoom, Subject $subject, Assignment $assignment, ?User $user = null)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);

        $actor = request()->user();
        $target = $user ?? $actor;

        $this->authorize('deleteLink', [$assignment, $target]);

        $updated = $assignment->users()->updateExistingPivot($target->id, ['submission_url' => null]);

        if (! $updated) {
            return back()->withErrors(['submission_url' => 'Tidak ada catatan pengumpulan untuk pengguna ini.']);
        }

        $this->recomputePivotStatus($assignment, $target);

        return back()->with('success', 'Tautan berhasil dihapus.');
    }

    private function sanitizeFileName(string $name): string
    {
        $name = str_replace(['/', '\\'], '_', $name);

        return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '');
    }

    private function syncGroupSubmissionProgress(Assignment $assignment, Subject $subject, User $submitter): void
    {
        $groupId = $submitter->groups()
            ->where('groups.subject_id', $subject->id)
            ->value('groups.id');

        if (! $groupId) {
            return;
        }

        $memberIds = DB::table('group_user')
            ->where('group_id', $groupId)
            ->where('user_id', '!=', $submitter->id)
            ->pluck('user_id');

        foreach ($memberIds as $memberId) {
            $assignment->users()->syncWithoutDetaching([
                $memberId => [
                    'status' => Assignment::STATUS_DONE,
                    'submitted_at' => now(),
                ],
            ]);
        }
    }

    private function recomputePivotStatus(Assignment $assignment, User $user): void
    {
        $row = $assignment->users()->whereKey($user->id)->first();

        if (! $row) {
            return;
        }

        $pivot = $row->pivot;

        if ($pivot->status === Assignment::STATUS_GRADED) {
            return;
        }

        $hasFile = $assignment->submissions()->where('user_id', $user->id)->exists();
        $hasLink = $assignment->requiresLink() && filled($pivot->submission_url);

        $assignment->users()->updateExistingPivot($user->id, [
            'status' => ($hasFile || $hasLink) ? Assignment::STATUS_DONE : Assignment::STATUS_PENDING,
            'submitted_at' => ($hasFile || $hasLink) ? ($pivot->submitted_at ?? now()) : null,
        ]);
    }

    private function normalizeSubmissionData(array $data): array
    {
        $data['submission_mode'] ??= Assignment::SUBMISSION_MODE_NONE;

        $usesFile = in_array($data['submission_mode'], [
            Assignment::SUBMISSION_MODE_FILE,
            Assignment::SUBMISSION_MODE_FILE_LINK,
        ]);

        if (! $usesFile) {
            $data['max_files'] = 1;
            $data['allowed_extensions'] = null;
            $data['max_file_size_kb'] = 10240;
        } else {
            $data['allowed_extensions'] = isset($data['allowed_extensions'])
                ? implode(',', $data['allowed_extensions'])
                : null;
            $data['max_files'] = $data['max_files'] ?? 1;
            $data['max_file_size_kb'] = $data['max_file_size_kb'] ?? 10240;
        }

        return $data;
    }

    public function grade(ClassRoom $classRoom, Subject $subject, Assignment $assignment, User $user)
    {
        abort_unless($assignment->subject_id === $subject->id, 404);
        $this->authorize('manage', $assignment);

        $validated = request()->validate([
            'grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        $updated = $assignment->users()->updateExistingPivot($user->id, [
            'grade' => $validated['grade'] ?? null,
            'feedback' => $validated['feedback'] ?? null,
            'status' => Assignment::STATUS_GRADED,
        ]);

        if (! $updated) {
            return back()->withErrors(['grade' => 'Tidak ada catatan pengumpulan untuk pengguna ini.']);
        }

        return back()->with('success', 'Progres berhasil diperbarui.');
    }
}
