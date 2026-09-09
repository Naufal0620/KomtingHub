<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuffleRun extends Model
{
    protected $fillable = [
        'subject_id',
        'user_id',
        'user_name',
        'algorithm',
        'version',
        'seed',
        'group_count',
        'members_per_group',
        'hash',
        'result',
        'member_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'member_snapshot' => 'array',
            'group_count' => 'integer',
            'members_per_group' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ShuffleLog::class);
    }
}
