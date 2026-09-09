<?php

namespace App\Http\Controllers;

use App\Exports\AssignmentExport;
use App\Exports\GroupExport;
use App\Models\ClassRoom;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function groups(Request $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        $this->audit($request->user(), $subject, 'groups');

        $filename = 'groups-'.Str::slug($classRoom->name).'-'.$subject->id.'-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new GroupExport($subject), $filename);
    }

    public function assignments(Request $request, ClassRoom $classRoom, Subject $subject)
    {
        abort_unless($subject->class_room_id === $classRoom->id, 404);
        $this->authorize('manage', $subject);

        $this->audit($request->user(), $subject, 'assignments');

        $filename = 'assignments-'.Str::slug($classRoom->name).'-'.$subject->id.'-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new AssignmentExport($subject), $filename);
    }

    private function audit($actor, Subject $subject, string $type): void
    {
        Log::channel('exports')->info("Excel export {$type}", [
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
            'subject_id' => $subject->id,
            'subject_name' => $subject->name,
            'class_room_id' => $subject->class_room_id,
            'exported_at' => now()->toDateTimeString(),
        ]);
    }
}