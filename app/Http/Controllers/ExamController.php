<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseExam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function show(string $slug): Response
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->with(['exam' => fn ($q) => $q->withCount('questions')])
            ->firstOrFail();

        $user = Auth::user();
        assert($user instanceof User);

        if (! $user->isEnrolledIn($course)) {
            abort(403);
        }

        $exam = $course->exam;

        $attempts = $exam instanceof CourseExam
            ? $exam->attempts()
                ->where('user_id', $user->id)
                ->orderByDesc('attempt_number')
                ->get()
                ->map(fn (ExamAttempt $a) => [
                    'id' => $a->id,
                    'attempt_number' => $a->attempt_number,
                    'status' => $a->status,
                    'started_at' => $a->started_at?->diffForHumans(),
                    'submitted_at' => $a->submitted_at?->diffForHumans(),
                    'score_points' => $a->score_points,
                    'max_points' => $a->max_points,
                    'needs_manual_review' => $a->needs_manual_review,
                    'passed' => $a->passed,
                ])
                ->values()
            : collect();

        $inProgressAttemptId = $attempts->firstWhere('status', 'in_progress')['id'] ?? null;

        return Inertia::render('Exams/Show', [
            'course' => [
                'slug' => $course->slug,
                'title' => $course->title,
            ],
            'exam' => $exam instanceof CourseExam ? [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description ?? '',
                'duration_minutes' => $exam->duration_minutes,
                'max_attempts' => $exam->max_attempts,
                'pass_score' => $exam->pass_score,
                'is_published' => $exam->is_published,
                'questions_count' => $exam->questions_count ?? $exam->questions()->count(),
            ] : null,
            'in_progress_attempt_id' => $inProgressAttemptId,
            'attempts' => $attempts,
        ]);
    }

    public function start(string $slug): RedirectResponse
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->with(['exam.questions'])
            ->firstOrFail();

        $user = Auth::user();
        assert($user instanceof User);

        if (! $user->isEnrolledIn($course)) {
            abort(403);
        }

        $exam = $course->exam;

        if (! ($exam instanceof CourseExam) || ! $exam->is_published) {
            abort(404);
        }

        if ($exam->questions->isEmpty()) {
            abort(404);
        }

        $objectiveMaxPoints = $exam->questions
            ->where('type', '!=', 'essay')
            ->sum('points');

        $needsManualReview = $exam->questions->contains(fn (ExamQuestion $q) => $q->type === 'essay');

        return DB::transaction(function () use ($course, $exam, $needsManualReview, $objectiveMaxPoints, $user) {
            CourseExam::whereKey($exam->id)->lockForUpdate()->first();

            $existingInProgress = $exam->attempts()
                ->where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->latest('started_at')
                ->first();

            if ($existingInProgress instanceof ExamAttempt) {
                return redirect()->route('courses.exam.take', [$course->slug, $existingInProgress]);
            }

            $attemptsCount = $exam->attempts()->where('user_id', $user->id)->count();

            if ($attemptsCount >= $exam->max_attempts) {
                return back()->with('error', 'Max attempts reached.');
            }

            $lastNumber = (int) $exam->attempts()
                ->where('user_id', $user->id)
                ->max('attempt_number');

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'attempt_number' => $lastNumber + 1,
                'started_at' => now(),
                'expires_at' => now()->addMinutes($exam->duration_minutes),
                'status' => 'in_progress',
                'score_points' => null,
                'max_points' => (int) $objectiveMaxPoints,
                'needs_manual_review' => $needsManualReview,
                'passed' => null,
            ]);

            return redirect()->route('courses.exam.take', [$course->slug, $attempt]);
        });
    }

    public function take(string $slug, ExamAttempt $attempt): Response|RedirectResponse
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->with(['exam.questions.options'])
            ->firstOrFail();

        $user = Auth::user();
        assert($user instanceof User);

        if (! $user->isEnrolledIn($course)) {
            abort(403);
        }

        $exam = $course->exam;

        if (! ($exam instanceof CourseExam) || $attempt->exam_id !== $exam->id) {
            abort(404);
        }

        if ($attempt->user_id !== $user->id) {
            abort(403);
        }

        if ($attempt->isExpired()) {
            $attempt->update([
                'status' => 'expired',
            ]);

            return redirect()
                ->route('courses.exam.show', $course->slug)
                ->with('error', 'Exam time expired.');
        }

        $attempt->load(['answers.selectedOptions']);

        $answersByQuestion = $attempt->answers->keyBy('question_id');

        return Inertia::render('Exams/Take', [
            'course' => [
                'slug' => $course->slug,
                'title' => $course->title,
            ],
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'duration_minutes' => $exam->duration_minutes,
                'pass_score' => $exam->pass_score,
            ],
            'attempt' => [
                'id' => $attempt->id,
                'attempt_number' => $attempt->attempt_number,
                'expires_at' => $attempt->expires_at?->toIso8601String(),
                'remaining_seconds' => max(0, now()->diffInSeconds($attempt->expires_at, false)),
            ],
            'questions' => $exam->questions->map(fn (ExamQuestion $q) => [
                'id' => $q->id,
                'type' => $q->type,
                'prompt' => $q->prompt,
                'points' => $q->points,
                'options' => $q->options->map(fn ($o) => [
                    'id' => $o->id,
                    'text' => $o->text,
                ])->values(),
                'answer' => $answersByQuestion->has($q->id) ? [
                    'answer_text' => $answersByQuestion[$q->id]->answer_text ?? '',
                    'selected_option_ids' => $answersByQuestion[$q->id]->selectedOptions->pluck('id')->all(),
                ] : null,
            ])->values(),
        ]);
    }

    public function submit(Request $request, string $slug, ExamAttempt $attempt): RedirectResponse
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->with(['exam.questions.options'])
            ->firstOrFail();

        $user = Auth::user();
        assert($user instanceof User);

        if (! $user->isEnrolledIn($course)) {
            abort(403);
        }

        $exam = $course->exam;

        if (! ($exam instanceof CourseExam) || $attempt->exam_id !== $exam->id) {
            abort(404);
        }

        if ($attempt->user_id !== $user->id) {
            abort(403);
        }

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('courses.exam.show', $course->slug);
        }

        if ($attempt->isExpired()) {
            $attempt->update([
                'status' => 'expired',
            ]);

            return redirect()
                ->route('courses.exam.show', $course->slug)
                ->with('error', 'Exam time expired.');
        }

        $data = $request->validate([
            'answers' => ['array', 'max:500'],
            'answers.*.question_id' => ['required', 'ulid'],
            'answers.*.selected_option_ids' => ['array', 'max:100'],
            'answers.*.selected_option_ids.*' => ['ulid'],
            'answers.*.answer_text' => ['nullable', 'string', 'max:10000'],
        ]);

        $answers = collect($data['answers'] ?? [])->keyBy('question_id');

        $needsManualReview = false;
        $scorePoints = 0;
        $objectiveMaxPoints = 0;

        DB::transaction(function () use ($answers, $attempt, $exam, &$needsManualReview, &$objectiveMaxPoints, &$scorePoints) {
            foreach ($exam->questions as $question) {
                $payload = $answers->get($question->id, []);

                $answer = ExamAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'answer_text' => $question->type === 'essay' ? ($payload['answer_text'] ?? '') : null,
                    ],
                );

                if ($question->type === 'essay') {
                    $needsManualReview = true;
                    $answer->update([
                        'is_correct' => null,
                        'points_awarded' => null,
                    ]);
                    $answer->selectedOptions()->sync([]);

                    continue;
                }

                $objectiveMaxPoints += $question->points;

                $selectedIds = array_values(array_unique($payload['selected_option_ids'] ?? []));

                $validSelectedIds = $question->options
                    ->whereIn('id', $selectedIds)
                    ->pluck('id')
                    ->all();

                $answer->selectedOptions()->sync($validSelectedIds);

                $correctIds = $question->options
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->values()
                    ->all();

                sort($validSelectedIds);
                sort($correctIds);

                $isCorrect = $validSelectedIds === $correctIds;

                $awarded = $isCorrect ? $question->points : 0;
                $scorePoints += $awarded;

                $answer->update([
                    'is_correct' => $isCorrect,
                    'points_awarded' => $awarded,
                ]);
            }

            $attemptUpdate = [
                'status' => 'submitted',
                'submitted_at' => now(),
                'score_points' => $scorePoints,
                'max_points' => $objectiveMaxPoints,
                'needs_manual_review' => $needsManualReview,
            ];

            if (! $needsManualReview && $objectiveMaxPoints > 0) {
                $percent = (int) floor(($scorePoints / $objectiveMaxPoints) * 100);
                $attemptUpdate['passed'] = $percent >= $exam->pass_score;
            } else {
                $attemptUpdate['passed'] = null;
            }

            $attempt->update($attemptUpdate);
        });

        return redirect()
            ->route('courses.exam.show', $course->slug)
            ->with('success', 'Exam submitted.');
    }
}
