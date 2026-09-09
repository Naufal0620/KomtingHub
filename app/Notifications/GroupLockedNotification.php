<?php

namespace App\Notifications;

use App\Models\Subject;
use Illuminate\Notifications\Notification;

class GroupLockedNotification extends Notification
{
    public function __construct(public Subject $subject)
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
            'type' => 'group_locked',
            'subject_id' => $this->subject->id,
            'subject_name' => $this->subject->name,
            'message' => "Kelompok untuk {$this->subject->name} telah dikunci.",
            'url' => route('class-rooms.subjects.show', [$this->subject->classRoom, $this->subject]),
        ];
    }
}