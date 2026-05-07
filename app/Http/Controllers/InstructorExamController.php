<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseExam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class InstructorExamController extends Controller
{
    public function edit(Course $course): Response
    {
        Gate::authorize('update', $course);

        $course->load(['exam.questions.options']);

        $exam = $course->exam;

        $attempts = $exam instanceof CourseExam
            ? $exam->attempts()
                ->with('user')
                ->latest('started_at')
                ->take(20)
                ->get()
                ->map(fn (ExamAttempt $a) => [
                    'id' => $a->id,
                    'user' => [
                        'id' => $a->user_id,
                        'name' => $a->user?->name ?? 'Unknown',
                    ],
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

        return Inertia::render('Instructor/ExamEditor', [
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
                'questions' => $exam->questions->map(fn (ExamQuestion $q) => [
                    'id' => $q->id,
                    'type' => $q->type,
                    'prompt' => $q->prompt,
                    'points' => $q->points,
                    'sort_order' => $q->sort_order,
                    'options' => $q->options->map(fn (ExamQuestionOption $o) => [
                        'id' => $o->id,
                        'text' => $o->text,
                        'is_correct' => $o->is_correct,
                        'sort_order' => $o->sort_order,
                    ])->values(),
                ])->values(),
            ] : null,
            'attempts' => $attempts,
        ]);
    }

    public function upsert(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:50'],
            'pass_score' => ['required', 'integer', 'min:0', 'max:100'],
            'is_published' => ['required', 'boolean'],
        ]);

        CourseExam::updateOrCreate(
            ['course_id' => $course->id],
            $data,
        );

        return back()->with('success', 'Exam saved.');
    }

    public function storeQuestion(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $exam = CourseExam::firstOrCreate(
            ['course_id' => $course->id],
            [
                'title' => 'Final Exam',
                'description' => null,
                'duration_minutes' => 30,
                'max_attempts' => 1,
                'pass_score' => 70,
                'is_published' => false,
            ],
        );

        $data = $request->validate([
            'type' => ['required', 'string', 'in:single,multiple,true_false,essay'],
            'prompt' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $nextOrder = (int) $exam->questions()->max('sort_order') + 1;

        $question = $exam->questions()->create([
            ...$data,
            'sort_order' => $nextOrder,
        ]);

        if ($data['type'] === 'true_false') {
            $question->options()->createMany([
                ['text' => 'True', 'is_correct' => false, 'sort_order' => 0],
                ['text' => 'False', 'is_correct' => false, 'sort_order' => 1],
            ]);
        }

        return back()->with('success', 'Question added.');
    }

    public function updateQuestion(Request $request, Course $course, ExamQuestion $question): RedirectResponse
    {
        Gate::authorize('update', $course);

        $exam = $course->exam;

        if (! ($exam instanceof CourseExam) || $question->exam_id !== $exam->id) {
            abort(404);
        }

        $data = $request->validate([
            'type' => ['required', 'string', 'in:single,multiple,true_false,essay'],
            'prompt' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['array'],
            'options.*.id' => ['nullable', 'string'],
            'options.*.text' => ['required_with:options', 'string', 'max:255'],
            'options.*.is_correct' => ['required_with:options', 'boolean'],
            'options.*.sort_order' => ['required_with:options', 'integer', 'min:0'],
        ]);

        $question->update([
            'type' => $data['type'],
            'prompt' => $data['prompt'],
            'points' => $data['points'],
        ]);

        if ($data['type'] === 'essay') {
            $question->options()->delete();
            return back()->with('success', 'Question saved.');
        }

        $optionsPayload = collect($data['options'] ?? [])
            ->map(fn (array $o) => [
                'id' => $o['id'] ?? null,
                'text' => $o['text'],
                'is_correct' => (bool) $o['is_correct'],
                'sort_order' => (int) $o['sort_order'],
            ])
            ->values();

        $existing = $question->options()->get()->keyBy('id');
        $keepIds = [];

        foreach ($optionsPayload as $opt) {
            $id = $opt['id'] ?? null;

            if ($id !== null && $existing->has($id)) {
                $existing[$id]->update([
                    'text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                    'sort_order' => $opt['sort_order'],
                ]);
                $keepIds[] = $id;
                continue;
            }

            $created = $question->options()->create([
                'text' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'sort_order' => $opt['sort_order'],
            ]);

            $keepIds[] = $created->id;
        }

        $question->options()
            ->whereNotIn('id', $keepIds)
            ->delete();

        return back()->with('success', 'Question saved.');
    }

    public function destroyQuestion(Course $course, ExamQuestion $question): RedirectResponse
    {
        Gate::authorize('update', $course);

        $exam = $course->exam;

        if (! ($exam instanceof CourseExam) || $question->exam_id !== $exam->id) {
            abort(404);
        }

        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    public function reorderQuestions(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $exam = $course->exam;

        if (! ($exam instanceof CourseExam)) {
            abort(404);
        }

        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['string'],
        ]);

        $questions = $exam->questions()->get()->keyBy('id');

        foreach (array_values($data['order']) as $index => $id) {
            if ($questions->has($id)) {
                $questions[$id]->update(['sort_order' => $index]);
            }
        }

        return back()->with('success', 'Reordered.');
    }

    public function showAttempt(Course $course, ExamAttempt $attempt): Response
    {
        Gate::authorize('update', $course);

        $exam = $course->exam;

        if (! ($exam instanceof CourseExam) || $attempt->exam_id !== $exam->id) {
            abort(404);
        }

        $attempt->load([
            'user',
            'answers.question.options',
            'answers.selectedOptions',
        ]);

        return Inertia::render('Instructor/ExamAttempt', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
            ],
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'pass_score' => $exam->pass_score,
            ],
            'attempt' => [
                'id' => $attempt->id,
                'attempt_number' => $attempt->attempt_number,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at?->toIso8601String(),
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
                'score_points' => $attempt->score_points,
                'max_points' => $attempt->max_points,
                'needs_manual_review' => $attempt->needs_manual_review,
                'passed' => $attempt->passed,
                'user' => [
                    'id' => $attempt->user_id,
                    'name' => $attempt->user?->name ?? 'Unknown',
                ],
            ],
            'answers' => $attempt->answers
                ->sortBy(fn (ExamAnswer $a) => $a->question?->sort_order ?? 0)
                ->values()
                ->map(fn (ExamAnswer $a) => [
                    'id' => $a->id,
                    'question' => [
                        'id' => $a->question_id,
                        'type' => $a->question?->type ?? 'essay',
                        'prompt' => $a->question?->prompt ?? '',
                        'points' => $a->question?->points ?? 0,
                    ],
                    'answer_text' => $a->answer_text ?? '',
                    'selected_options' => $a->selectedOptions->map(fn ($o) => [
                        'id' => $o->id,
                        'text' => $o->text,
                    ])->values(),
                    'points_awarded' => $a->points_awarded,
                    'is_correct' => $a->is_correct,
                ]),
        ]);
    }

    public function gradeAttempt(Request $request, Course $course, ExamAttempt $attempt): RedirectResponse
    {
        Gate::authorize('update', $course);

        $exam = $course->exam;

        if (! ($exam instanceof CourseExam) || $attempt->exam_id !== $exam->id) {
            abort(404);
        }

        $data = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.answer_id' => ['required', 'string'],
            'grades.*.points_awarded' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $attempt->load(['answers.question']);

        $answersById = $attempt->answers->keyBy('id');

        foreach ($data['grades'] as $grade) {
            $answerId = $grade['answer_id'];

            if (! $answersById->has($answerId)) {
                continue;
            }

            $answer = $answersById[$answerId];

            if (($answer->question?->type ?? null) !== 'essay') {
                continue;
            }

            $maxPoints = (int) ($answer->question?->points ?? 0);
            $awarded = min($maxPoints, (int) $grade['points_awarded']);

            $answer->update([
                'points_awarded' => $awarded,
                'is_correct' => null,
            ]);
        }

        $attempt->load(['answers.question']);

        $totalMax = $attempt->answers->sum(fn (ExamAnswer $a) => (int) ($a->question?->points ?? 0));
        $totalScore = $attempt->answers->sum(fn (ExamAnswer $a) => (int) ($a->points_awarded ?? 0));

        $passed = null;
        if ($totalMax > 0) {
            $percent = (int) floor(($totalScore / $totalMax) * 100);
            $passed = $percent >= $exam->pass_score;
        }

        $attempt->update([
            'score_points' => $totalScore,
            'max_points' => $totalMax,
            'needs_manual_review' => false,
            'passed' => $passed,
        ]);

        return back()->with('success', 'Graded.');
    }
}

