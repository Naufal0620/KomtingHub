<?php

namespace App\Notifications;

use App\Models\Subject;
use Illuminate\Notifications\Notification;

class GroupUnlockedNotification extends Notification
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
            'type' => 'group_unlocked',
            'subject_id' => $this->subject->id,
            'subject_name' => $this->subject->name,
            'message' => "Kelompok untuk {$this->subject->name} telah dibuka kembali.",
            'url' => route('class-rooms.subjects.show', [$this->subject->classRoom, $this->subject]),
        ];
    }
}