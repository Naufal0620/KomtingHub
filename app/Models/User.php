<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_KOMTING = 'komting';

    public const ROLE_STUDENT = 'student';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_KOMTING,
        self::ROLE_STUDENT,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isKomting(): bool
    {
        return $this->role === self::ROLE_KOMTING;
    }

    public function isKomtingOrAdmin(): bool
    {
        return $this->isKomting() || $this->isAdmin();
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function classRooms(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_user')
            ->withTimestamps();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)
            ->withTimestamps();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->withPivot(['locked_at'])
            ->withTimestamps();
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class)
            ->withPivot(['status', 'submitted_at', 'grade', 'feedback'])
            ->withTimestamps();
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function managedClassRooms(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'komting_id');
    }
}
