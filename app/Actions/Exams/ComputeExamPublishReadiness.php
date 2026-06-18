<?php

namespace App\Actions\Exams;

use App\Models\CourseExam;

class ComputeExamPublishReadiness
{
    /**
     * @return array{
     *     blockers: array<int, array{message: string, question_id: string|null}>,
     *     warnings: array<int, array{message: string, question_id: string|null}>,
     *     summary: array{
     *         total_questions: int,
     *         objective_count: int,
     *         essay_count: int,
     *         total_points: int,
     *         objective_points: int,
     *         blockers_count: int,
     *         warnings_count: int,
     *         can_publish: bool
     *     }
     * }
     */
    public function __invoke(?CourseExam $exam): array
    {
        $blockers = [];
        $warnings = [];

        if (! $exam instanceof CourseExam) {
            $blockers[] = [
                'message' => 'Tambahkan minimal 1 pertanyaan sebelum publish.',
                'question_id' => null,
            ];

            return $this->formatResult([], $blockers, $warnings);
        }

        $exam->loadMissing('questions.options');
        $questions = $exam->questions->sortBy('sort_order')->values();

        if ($questions->isEmpty()) {
            $blockers[] = [
                'message' => 'Tambahkan minimal 1 pertanyaan sebelum publish.',
                'question_id' => null,
            ];
        }

        foreach ($questions as $index => $question) {
            $label = 'Soal #'.($index + 1);

            if (! filled(trim((string) $question->prompt))) {
                $blockers[] = [
                    'message' => "{$label}: prompt masih kosong.",
                    'question_id' => $question->id,
                ];
            }

            if ($question->type === 'essay') {
                continue;
            }

            if ($question->options->isEmpty()) {
                $blockers[] = [
                    'message' => "{$label}: wajib punya opsi jawaban.",
                    'question_id' => $question->id,
                ];

                continue;
            }

            if ($question->options->contains(fn ($option) => ! filled(trim((string) $option->text)))) {
                $blockers[] = [
                    'message' => "{$label}: ada opsi yang masih kosong.",
                    'question_id' => $question->id,
                ];
            }

            $correctOptionsCount = $question->options->where('is_correct', true)->count();

            if ($question->type === 'multiple' && $correctOptionsCount < 1) {
                $blockers[] = [
                    'message' => "{$label}: minimal 1 jawaban harus ditandai benar.",
                    'question_id' => $question->id,
                ];
            }

            if (in_array($question->type, ['single', 'true_false'], true) && $correctOptionsCount !== 1) {
                $blockers[] = [
                    'message' => "{$label}: harus punya tepat 1 jawaban benar.",
                    'question_id' => $question->id,
                ];
            }
        }

        return $this->formatResult($questions->all(), $blockers, $warnings);
    }

    /**
     * @param  array<int, mixed>  $questions
     * @param  array<int, array{message: string, question_id: string|null}>  $blockers
     * @param  array<int, array{message: string, question_id: string|null}>  $warnings
     * @return array{
     *     blockers: array<int, array{message: string, question_id: string|null}>,
     *     warnings: array<int, array{message: string, question_id: string|null}>,
     *     summary: array{
     *         total_questions: int,
     *         objective_count: int,
     *         essay_count: int,
     *         total_points: int,
     *         objective_points: int,
     *         blockers_count: int,
     *         warnings_count: int,
     *         can_publish: bool
     *     }
     * }
     */
    private function formatResult(array $questions, array $blockers, array $warnings): array
    {
        $essayCount = collect($questions)
            ->filter(fn ($question) => ($question->type ?? null) === 'essay')
            ->count();

        $objectiveCount = count($questions) - $essayCount;
        $totalPoints = (int) collect($questions)->sum(fn ($question) => (int) ($question->points ?? 0));
        $objectivePoints = (int) collect($questions)
            ->filter(fn ($question) => ($question->type ?? null) !== 'essay')
            ->sum(fn ($question) => (int) ($question->points ?? 0));

        return [
            'blockers' => $blockers,
            'warnings' => $warnings,
            'summary' => [
                'total_questions' => count($questions),
                'objective_count' => $objectiveCount,
                'essay_count' => $essayCount,
                'total_points' => $totalPoints,
                'objective_points' => $objectivePoints,
                'blockers_count' => count($blockers),
                'warnings_count' => count($warnings),
                'can_publish' => count($blockers) === 0,
            ],
        ];
    }
}
