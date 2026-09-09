<?php

namespace App\Exports;

use App\Models\Subject;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class GroupExport implements FromQuery, WithMapping, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(protected Subject $subject)
    {
        $this->subject->load('classRoom');
    }

    public function query(): Builder
    {
        return DB::query()
            ->fromSub(
                DB::table('subject_user')
                    ->join('users', 'users.id', '=', 'subject_user.user_id')
                    ->where('subject_user.subject_id', $this->subject->id)
                    ->select(
                        'users.id as user_id',
                        'users.name as student_name',
                        'users.email as student_email',
                    ),
                'members'
            )
            ->leftJoin('group_user', function ($join) {
                $join->on('group_user.user_id', '=', 'members.user_id')
                    ->whereIn('group_user.group_id', $this->subject->groups()->select('id'));
            })
            ->leftJoin('groups', 'groups.id', '=', 'group_user.group_id')
            ->select(
                'members.student_name',
                'members.student_email',
                'groups.name as group_name',
            )
            ->orderBy('members.user_id');
    }

    public function map($row): array
    {
        return [
            $this->subject->classRoom->name,
            $this->subject->name,
            $row->group_name ?? 'Belum di kelompok',
            $row->student_name,
            $row->student_email,
        ];
    }

    public function headings(): array
    {
        return ['Kelas', 'Mata Pelajaran', 'Kelompok', 'Nama Mahasiswa', 'Email Mahasiswa'];
    }

    public function title(): string
    {
        return 'Kelompok';
    }
}