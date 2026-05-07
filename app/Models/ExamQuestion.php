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
    'type',
    'prompt',
    'points',
    'sort_order',
])]
class ExamQuestion extends Model
{
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'sort_order' => 'integer',
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
     * @return HasMany<ExamQuestionOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(ExamQuestionOption::class, 'question_id')->orderBy('sort_order');
    }
}

