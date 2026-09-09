<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuffleLog extends Model
{
    protected $fillable = [
        'shuffle_run_id',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function shuffleRun(): BelongsTo
    {
        return $this->belongsTo(ShuffleRun::class);
    }
}
