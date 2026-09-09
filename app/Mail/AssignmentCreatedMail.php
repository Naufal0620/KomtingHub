<?php

namespace App\Mail;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssignmentCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Assignment $assignment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Tugas baru: {$this->assignment->title}");
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.assignment-created',
            with: [
                'assignment' => $this->assignment,
                'url' => route('class-rooms.subjects.assignments.show', [
                    $this->assignment->subject->classRoom,
                    $this->assignment->subject,
                    $this->assignment,
                ]),
            ],
        );
    }
}