<?php

namespace App\Mail;

use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public bool $locked;

    public function __construct(public Subject $subjectModel, bool $locked = true)
    {
        $this->locked = $locked;
    }

    public function envelope(): Envelope
    {
        $subject = $this->locked
            ? "Kelompok dikunci untuk {$this->subjectModel->name}"
            : "Kelompok dibuka kembali untuk {$this->subjectModel->name}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.group-status',
            with: [
                'subject' => $this->subjectModel,
                'locked' => $this->locked,
                'url' => route('class-rooms.subjects.show', [$this->subjectModel->classRoom, $this->subjectModel]),
            ],
        );
    }
}