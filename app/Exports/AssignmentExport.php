<?php

namespace App\Exports;

use App\Models\Subject;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AssignmentExport implements FromQuery, WithMapping, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(protected Subject $subject)
    {
        $this->subject->load('classRoom');
    }

    public function query(): Builder
    {
        return DB::table('assignment_user')
            ->join('assignments', 'assignments.id', '=', 'assignment_user.assignment_id')
            ->join('users', 'users.id', '=', 'assignment_user.user_id')
            ->leftJoin('group_user', function ($join) {
                $join->on('group_user.user_id', '=', 'users.id')
                    ->whereIn('group_user.group_id', $this->subject->groups()->select('id'));
            })
            ->leftJoin('groups', 'groups.id', '=', 'group_user.group_id')
            ->where('assignments.subject_id', $this->subject->id)
            ->select(
                'assignments.title as title',
                'assignments.type as type',
                'assignments.due_date as due_date',
                'users.name as student_name',
                'users.email as student_email',
                'groups.name as group_name',
                'assignment_user.status as status',
                'assignment_user.submitted_at as submitted_at',
                'assignment_user.submission_url as submission_url',
                'assignment_user.grade as grade',
                'assignment_user.feedback as feedback',
            )
            ->orderBy('users.id')
            ->orderBy('assignments.id');
    }

    public function map($row): array
    {
        return [
            $this->subject->name,
            $row->title,
            $row->type === 'group' ? 'Kelompok' : 'Individu',
            $this->formatDate($row->due_date),
            $row->student_name,
            $row->student_email,
            $row->group_name ?? 'Belum di kelompok',
            $this->statusLabel((string) $row->status),
            $this->formatDateTime($row->submitted_at),
            $row->submission_url ?? '',
            $row->grade ?? '',
            $row->feedback ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Mata Pelajaran', 'Tugas', 'Tipe', 'Tanggal Tenggat', 'Nama Mahasiswa',
            'Email Mahasiswa', 'Kelompok', 'Status', 'Waktu Dikumpulkan', 'Tautan', 'Nilai', 'Umpan Balik',
        ];
    }

    public function title(): string
    {
        return 'Tugas';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'graded' => 'Dinilai',
            'done' => 'Selesai',
            default => 'Tertunda',
        };
    }

    private function formatDate(mixed $value): string
    {
        return $value === null ? '' : Carbon::parse($value)->format('Y-m-d');
    }

    private function formatDateTime(mixed $value): string
    {
        return $value === null ? '' : Carbon::parse($value)->format('Y-m-d H:i');
    }
}