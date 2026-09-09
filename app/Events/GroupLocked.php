<?php

namespace App\Events;

use App\Models\Subject;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupLocked
{
    use Dispatchable, SerializesModels;

    public function __construct(public Subject $subject)
    {
    }
}
