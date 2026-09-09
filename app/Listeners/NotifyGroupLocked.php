<?php

namespace App\Listeners;

use App\Events\GroupLocked;
use App\Mail\GroupStatusMail;
use App\Models\Subject;
use App\Notifications\GroupLockedNotification;
use Illuminate\Support\Facades\Mail;

class NotifyGroupLocked
{
    public function handle(GroupLocked $event): void
    {
        $subject = $event->subject;

        if (! $subject instanceof Subject || ! $subject->exists) {
            return;
        }

        $subject->members->each(function ($member) use ($subject) {
            $member->notify(new GroupLockedNotification($subject));
            Mail::to($member)->queue(new GroupStatusMail($subject, locked: true));
        });
    }
}