<?php

namespace App\Listeners;

use App\Events\GroupUnlocked;
use App\Mail\GroupStatusMail;
use App\Models\Subject;
use App\Notifications\GroupUnlockedNotification;
use Illuminate\Support\Facades\Mail;

class NotifyGroupUnlocked
{
    public function handle(GroupUnlocked $event): void
    {
        $subject = $event->subject;

        if (! $subject instanceof Subject || ! $subject->exists) {
            return;
        }

        $subject->members->each(function ($member) use ($subject) {
            $member->notify(new GroupUnlockedNotification($subject));
            Mail::to($member)->queue(new GroupStatusMail($subject, locked: false));
        });
    }
}