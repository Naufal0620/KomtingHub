<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Notification;

class AssignmentCreatedNotification extends Notification
{
    public function __construct(public Assignment $assignment)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assignment_created',
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'subject_name' => $this->assignment->subject->name,
            'message' => "Tugas baru: {$this->assignment->title}",
            'url' => route('class-rooms.subjects.assignments.show', [
                $this->assignment->subject->classRoom,
                $this->assignment->subject,
                $this->assignment,
            ]),
        ];
    }
}