<?php

namespace App\Events;

use App\Models\Subject;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupUnlocked
{
    use Dispatchable, SerializesModels;

    public function __construct(public Subject $subject)
    {
    }
}