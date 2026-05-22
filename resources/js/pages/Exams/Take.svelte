<script lang="ts">
    import { onDestroy } from 'svelte';
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
    import { Textarea } from '@/components/ui/textarea';

    type ExamOption = { id: string; text: string };

    type ExamQuestion = {
        id: string;
        type: 'single' | 'multiple' | 'true_false' | 'essay';
        prompt: string;
        points: number;
        options: ExamOption[];
        answer: { answer_text: string; selected_option_ids: string[] } | null;
    };

    let {
        course,
        exam,
        attempt,
        questions,
    }: {
        course: { slug: string; title: string };
        exam: { id: string; title: string; duration_minutes: number; pass_score: number };
        attempt: { id: string; attempt_number: number; expires_at: string; remaining_seconds: number };
        questions: ExamQuestion[];
    } = $props();

    let remaining = $state(0);
    let submitting = $state(false);

    type AnswerDraft = { question_id: string; selected_option_ids: string[]; answer_text: string };
    let draft = $state<Record<string, AnswerDraft>>({});

    function ensureDraft(questionId: string): AnswerDraft {
        const existing = draft[questionId];

        if (existing) {
            return existing;
        }

        const next = {
            question_id: questionId,
            selected_option_ids: [],
            answer_text: '',
        };

        draft = {
            ...draft,
            [questionId]: next,
        };

        return next;
    }

    $effect(() => {
        remaining = attempt.remaining_seconds;
    });

    $effect(() => {
        const next: Record<string, AnswerDraft> = {};

        for (const q of questions) {
            next[q.id] = {
                question_id: q.id,
                selected_option_ids: q.answer?.selected_option_ids ?? [],
                answer_text: q.answer?.answer_text ?? '',
            };
        }

        draft = next;
    });

    const timer = setInterval(() => {
        remaining = Math.max(0, remaining - 1);
        if (remaining === 0) {
            submit();
        }
    }, 1000);

    onDestroy(() => clearInterval(timer));

    function toggleOption(questionId: string, optionId: string): void {
        const current = ensureDraft(questionId);
        const next = new Set(current.selected_option_ids);

        if (next.has(optionId)) {
            next.delete(optionId);
        } else {
            next.add(optionId);
        }

        current.selected_option_ids = Array.from(next);
    }

    function setSingleOption(questionId: string, optionId: string): void {
        ensureDraft(questionId).selected_option_ids = [optionId];
    }

    function setEssayAnswer(questionId: string, value: string): void {
        ensureDraft(questionId).answer_text = value;
    }

    function getSelectedOptionIds(questionId: string): string[] {
        return draft[questionId]?.selected_option_ids ?? [];
    }

    function getAnswerText(questionId: string): string {
        return draft[questionId]?.answer_text ?? '';
    }

    function submit(): void {
        if (submitting) return;
        submitting = true;

        const answers = Object.values(draft).map((a) => ({
            question_id: a.question_id,
            selected_option_ids: a.selected_option_ids,
            answer_text: a.answer_text,
        }));

        router.post(
            `/courses/${course.slug}/exam/attempts/${attempt.id}/submit`,
            { answers },
            { preserveScroll: true },
        );
    }

    function formatTime(seconds: number): string {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m}:${String(s).padStart(2, '0')}`;
    }
</script>

<AppHead title={`Take Exam · ${course.title}`} />

<div class="mx-auto w-full max-w-3xl px-4 py-8">
    <Card>
        <CardHeader class="flex flex-row items-start justify-between gap-4">
            <div>
                <CardTitle>{exam.title}</CardTitle>
                <p class="mt-1 text-sm text-muted-foreground">
                    Attempt #{attempt.attempt_number}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-muted-foreground">Time left</p>
                <p class="text-lg font-semibold tabular-nums">
                    {formatTime(remaining)}
                </p>
            </div>
        </CardHeader>
        <CardContent>
            <p class="text-sm text-muted-foreground">
                Pass score: {exam.pass_score}%
            </p>
        </CardContent>
    </Card>

    <div class="mt-6 space-y-4">
        {#each questions as q, index (q.id)}
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        {index + 1}. {q.prompt}
                    </CardTitle>
                    <p class="text-xs text-muted-foreground">{q.points} point(s)</p>
                </CardHeader>
                <CardContent class="space-y-3">
                    {#if q.type === 'essay'}
                        <div class="space-y-2">
                            <Label for={`q-${q.id}`}>Answer</Label>
                            <Textarea
                                id={`q-${q.id}`}
                                rows={5}
                                value={getAnswerText(q.id)}
                                oninput={(event) => setEssayAnswer(q.id, event.currentTarget.value)}
                            />
                        </div>
                    {:else}
                        <div class="space-y-2">
                            {#each q.options as opt (opt.id)}
                                <div class="flex items-start gap-2">
                                    {#if q.type === 'multiple'}
                                        <input
                                            type="checkbox"
                                            class="mt-1 size-4 accent-primary"
                                            checked={getSelectedOptionIds(q.id).includes(opt.id)}
                                            onchange={() => toggleOption(q.id, opt.id)}
                                        />
                                    {:else}
                                        <input
                                            type="radio"
                                            name={`q-${q.id}`}
                                            class="mt-1 size-4 accent-primary"
                                            checked={getSelectedOptionIds(q.id)[0] === opt.id}
                                            onchange={() => setSingleOption(q.id, opt.id)}
                                        />
                                    {/if}
                                    <Label class="leading-6">{opt.text}</Label>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </CardContent>
            </Card>
        {/each}
    </div>

    <Separator class="my-6" />

    <Button size="lg" class="w-full" disabled={submitting} onclick={submit}>
        Submit exam
    </Button>
</div>
