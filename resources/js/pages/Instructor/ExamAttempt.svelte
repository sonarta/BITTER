<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Instructor', href: '/instructor' },
            { title: 'Courses', href: '/instructor/courses' },
            { title: 'Exam Attempt', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';

    type AnswerRow = {
        id: string;
        question: { id: string; type: string; prompt: string; points: number };
        answer_text: string;
        selected_options: { id: string; text: string }[];
        points_awarded: number | null;
        is_correct: boolean | null;
    };

    let { course, attempt, answers }: {
        course: { id: string; title: string; slug: string };
        attempt: {
            id: string;
            attempt_number: number;
            status: string;
            started_at: string | null;
            submitted_at: string | null;
            score_points: number | null;
            max_points: number;
            needs_manual_review: boolean;
            passed: boolean | null;
            user: { id: number; name: string };
        };
        answers: AnswerRow[];
    } = $props();

    let grades = $state<Record<string, number>>({});
    let gradesInitialized = $state(false);

    $effect(() => {
        if (gradesInitialized) return;

        const next: Record<string, number> = {};
        for (const a of answers) {
            if (a.question.type === 'essay') {
                next[a.id] = a.points_awarded ?? 0;
            }
        }

        grades = next;
        gradesInitialized = true;
    });

    function submitGrades(): void {
        const payload = Object.entries(grades).map(([answer_id, points_awarded]) => ({
            answer_id,
            points_awarded,
        }));

        router.post(`/instructor/courses/${course.id}/exam/attempts/${attempt.id}/grade`, {
            grades: payload,
        });
    }
</script>

<AppHead title={`Exam Attempt · ${course.title}`} />

<div class="mx-auto w-full max-w-4xl px-4 py-8 space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <Link href={`/instructor/courses/${course.id}/exam`} class="text-sm text-muted-foreground hover:text-foreground">
                ← Back to exam
            </Link>
            <h1 class="mt-2 text-2xl font-bold">
                {attempt.user.name} · Attempt #{attempt.attempt_number}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <Badge variant="outline">{attempt.status}</Badge>
                {#if attempt.needs_manual_review}
                    <Badge variant="outline">Needs manual review</Badge>
                {/if}
                {#if attempt.passed === true}
                    <Badge variant="secondary">Passed</Badge>
                {:else if attempt.passed === false}
                    <Badge variant="outline">Not passed</Badge>
                {/if}
                <Badge variant="outline">
                    Score: {attempt.score_points ?? 0}/{attempt.max_points}
                </Badge>
            </div>
        </div>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>Answers</CardTitle>
        </CardHeader>
        <CardContent class="space-y-5">
            {#each answers as a, idx (a.id)}
                <div class="space-y-2">
                    <div class="text-sm font-medium">
                        {idx + 1}. {a.question.prompt}
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                        <Badge variant="outline">{a.question.type}</Badge>
                        <Badge variant="outline">{a.question.points} pt</Badge>
                        {#if a.is_correct === true}
                            <Badge variant="secondary">Correct</Badge>
                        {:else if a.is_correct === false}
                            <Badge variant="outline">Incorrect</Badge>
                        {/if}
                    </div>

                    {#if a.question.type === 'essay'}
                        <p class="rounded-md border bg-muted/30 p-3 text-sm whitespace-pre-wrap">{a.answer_text || '—'}</p>
                        <div class="flex items-end gap-3">
                            <div class="w-40 space-y-1.5">
                                <Label>Points awarded</Label>
                                <Input type="number" bind:value={grades[a.id]} />
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Max: {a.question.points}
                            </p>
                        </div>
                    {:else}
                        <ul class="list-disc pl-5 text-sm">
                            {#if a.selected_options.length === 0}
                                <li class="text-muted-foreground">No answer</li>
                            {:else}
                                {#each a.selected_options as o (o.id)}
                                    <li>{o.text}</li>
                                {/each}
                            {/if}
                        </ul>
                    {/if}
                </div>
                {#if idx !== answers.length - 1}
                    <Separator />
                {/if}
            {/each}

            {#if Object.keys(grades).length > 0}
                <Button onclick={submitGrades}>Save essay grades</Button>
            {/if}
        </CardContent>
    </Card>
</div>
