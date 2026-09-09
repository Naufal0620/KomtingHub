<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    public const TYPE_INDIVIDUAL = 'individual';

    public const TYPE_GROUP = 'group';

    public const STATUS_PENDING = 'pending';

    public const STATUS_DONE = 'done';

    public const STATUS_GRADED = 'graded';

    public const SUBMISSION_MODE_NONE = 'none';

    public const SUBMISSION_MODE_FILE = 'file';

    public const SUBMISSION_MODE_LINK = 'link';

    public const SUBMISSION_MODE_FILE_LINK = 'file_link';

    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'due_date',
        'type',
        'submission_mode',
        'max_files',
        'allowed_extensions',
        'max_file_size_kb',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'max_files' => 'integer',
            'max_file_size_kb' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['status', 'submitted_at', 'submission_url', 'grade', 'feedback'])
            ->withCasts(['submitted_at' => 'datetime'])
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function requiresFile(): bool
    {
        return in_array($this->submission_mode, [self::SUBMISSION_MODE_FILE, self::SUBMISSION_MODE_FILE_LINK]);
    }

    public function requiresLink(): bool
    {
        return in_array($this->submission_mode, [self::SUBMISSION_MODE_LINK, self::SUBMISSION_MODE_FILE_LINK]);
    }

    public static function submissionModes(): array
    {
        return [
            self::SUBMISSION_MODE_NONE,
            self::SUBMISSION_MODE_FILE,
            self::SUBMISSION_MODE_LINK,
            self::SUBMISSION_MODE_FILE_LINK,
        ];
    }

    public function allowedExtensionList(): array
    {
        if (! $this->allowed_extensions) {
            return $this->defaultExtensionList();
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->allowed_extensions))));
    }

    public static function defaultExtensionList(): array
    {
        return ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
    }

    public function maxAllowedSizeBytes(): int
    {
        return $this->max_file_size_kb * 1024;
    }

    public function isLate(DateTimeInterface $at): bool
    {
        return $this->due_date !== null && $at->getTimestamp() > $this->due_date->getTimestamp();
    }
}
