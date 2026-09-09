<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'komting_id',
    ];

    public function komting(): BelongsTo
    {
        return $this->belongsTo(User::class, 'komting_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_user')
            ->withTimestamps();
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}
