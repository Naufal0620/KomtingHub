<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    public const GROUP_MODE_RANDOM = 'random';

    public const GROUP_MODE_SELECT = 'select';

    protected $fillable = [
        'class_room_id',
        'name',
        'code',
        'description',
        'group_mode',
        'groups_locked',
    ];

    protected function casts(): array
    {
        return [
            'groups_locked' => 'boolean',
        ];
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function shuffleRuns(): HasMany
    {
        return $this->hasMany(ShuffleRun::class);
    }

    public function isRandomMode(): bool
    {
        return $this->group_mode === self::GROUP_MODE_RANDOM;
    }

    public function isSelectMode(): bool
    {
        return $this->group_mode === self::GROUP_MODE_SELECT;
    }

    public function mustAllowSelfSelection(): void
    {
        abort_unless($this->isSelectMode(), 403, 'Pemilihan mandiri tidak diaktifkan untuk mata pelajaran ini.');
    }
}
