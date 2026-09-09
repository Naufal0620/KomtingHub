<?php

namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Mail\AssignmentCreatedMail;
use App\Models\Assignment;
use App\Notifications\AssignmentCreatedNotification;
use Illuminate\Support\Facades\Mail;

class NotifyAssignmentCreated
{
    public function handle(AssignmentCreated $event): void
    {
        $assignment = $event->assignment;

        if (! $assignment instanceof Assignment || ! $assignment->exists) {
            return;
        }

        $subject = $assignment->subject;

        if ($subject === null) {
            return;
        }

        $subject->members->each(function ($member) use ($assignment) {
            $member->notify(new AssignmentCreatedNotification($assignment));
            Mail::to($member)->queue(new AssignmentCreatedMail($assignment));
        });
    }
}