<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'exam_id',
    'user_id',
    'attempt_number',
    'started_at',
    'expires_at',
    'submitted_at',
    'status',
    'score_points',
    'max_points',
    'needs_manual_review',
    'passed',
])]
class ExamAttempt extends Model
{
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score_points' => 'integer',
            'max_points' => 'integer',
            'needs_manual_review' => 'boolean',
            'passed' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CourseExam, $this>
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(CourseExam::class, 'exam_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ExamAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'attempt_id');
    }

    public function isExpired(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }

        if ($this->expires_at === null) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }
}
