<?php

namespace App\Actions\Exams;

use App\Models\Course;
use App\Models\CourseExam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use Illuminate\Http\Request;

class BuildInstructorExamEditorData
{
    public function __construct(
        private readonly ComputeExamPublishReadiness $computeExamPublishReadiness,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function __invoke(Course $course, Request $request): array
    {
        $course->load(['exam.questions.options']);

        $exam = $course->exam;
        $attempts = $this->buildAttempts($exam);
        $readiness = ($this->computeExamPublishReadiness)($exam instanceof CourseExam ? $exam : null);

        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'status' => $course->status,
            ],
            'exam' => $exam instanceof CourseExam ? [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description ?? '',
                'duration_minutes' => $exam->duration_minutes,
                'max_attempts' => $exam->max_attempts,
                'pass_score' => $exam->pass_score,
                'is_published' => $exam->is_published,
                'questions' => $exam->questions
                    ->sortBy('sort_order')
                    ->values()
                    ->map(fn (ExamQuestion $question) => [
                        'id' => $question->id,
                        'type' => $question->type,
                        'prompt' => $question->prompt,
                        'points' => $question->points,
                        'sort_order' => $question->sort_order,
                        'options' => $question->options
                            ->sortBy('sort_order')
                            ->values()
                            ->map(fn (ExamQuestionOption $option) => [
                                'id' => $option->id,
                                'text' => $option->text,
                                'is_correct' => $option->is_correct,
                                'sort_order' => $option->sort_order,
                            ])
                            ->all(),
                    ])
                    ->all(),
            ] : null,
            'attempts' => $attempts,
            'readiness' => $readiness,
            'ui' => [
                'active_tab' => $this->resolveActiveTab($request),
                'features' => [
                    'exam_editor_v2' => (bool) config('features.exam_editor_v2'),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAttempts(?CourseExam $exam): array
    {
        if (! $exam instanceof CourseExam) {
            return [];
        }

        return $exam->attempts()
            ->with('user')
            ->latest('started_at')
            ->take(20)
            ->get()
            ->map(fn (ExamAttempt $attempt) => [
                'id' => $attempt->id,
                'user' => [
                    'id' => $attempt->user_id,
                    'name' => $attempt->user?->name ?? 'Unknown',
                ],
                'attempt_number' => $attempt->attempt_number,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at?->toIso8601String(),
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
                'score_points' => $attempt->score_points,
                'max_points' => $attempt->max_points,
                'needs_manual_review' => $attempt->needs_manual_review,
                'passed' => $attempt->passed,
            ])
            ->values()
            ->all();
    }

    private function resolveActiveTab(Request $request): string
    {
        $tab = $request->string('tab')->value();
        $allowedTabs = ['overview', 'builder', 'preview', 'attempts'];

        return in_array($tab, $allowedTabs, true) ? $tab : 'overview';
    }
}
