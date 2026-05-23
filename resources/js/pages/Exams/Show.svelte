<script module lang="ts">
    import { index as coursesIndex } from '@/routes/courses';

    export const layout = {
        breadcrumbs: [
            { title: 'Courses', href: coursesIndex() },
            { title: 'Exam', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import { show as courseShow } from '@/routes/courses';
    import { start as examStart, take as examTake } from '@/routes/courses/exam';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

    let {
        course,
        exam = null,
        in_progress_attempt_id = null,
        attempts = [],
    }: {
        course: { slug: string; title: string };
        exam: {
            id: string;
            title: string;
            description: string;
            duration_minutes: number;
            max_attempts: number;
            pass_score: number;
            is_published: boolean;
            questions_count: number;
        } | null;
        in_progress_attempt_id: string | null;
        attempts: {
            id: string;
            attempt_number: number;
            status: string;
            started_at: string | null;
            submitted_at: string | null;
            score_points: number | null;
            max_points: number;
            needs_manual_review: boolean;
            passed: boolean | null;
        }[];
    } = $props();

    let starting = $state(false);

    function start(): void {
        router.post(examStart.url(course.slug), {}, {
            preserveScroll: true,
            onStart: () => {
                starting = true;
            },
            onFinish: () => {
                starting = false;
            },
        });
    }
</script>

<AppHead title={`Exam · ${course.title}`} />

<div class="mx-auto w-full max-w-3xl px-4 py-8">
    <div class="mb-6">
        <Link href={courseShow.url(course.slug)} class="text-sm text-muted-foreground hover:text-foreground">
            ← Back to course
        </Link>
        <h1 class="mt-2 text-2xl font-bold">{course.title}</h1>
        <p class="text-sm text-muted-foreground">Final exam</p>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>{exam?.title ?? 'Exam not available'}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            {#if !exam}
                <p class="text-sm text-muted-foreground">
                    Course ini belum punya exam.
                </p>
            {:else if !exam.is_published}
                <p class="text-sm text-muted-foreground">
                    Exam sudah dibuat, tapi belum dipublish oleh instructor.
                </p>
            {:else}
                {#if exam.description}
                    <p class="text-sm text-muted-foreground">{exam.description}</p>
                {/if}

                <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                    <Badge variant="outline">{exam.questions_count} questions</Badge>
                    <Badge variant="outline">{exam.duration_minutes} minutes</Badge>
                    <Badge variant="outline">Pass: {exam.pass_score}%</Badge>
                    <Badge variant="outline">Max attempts: {exam.max_attempts}</Badge>
                </div>

                {#if in_progress_attempt_id}
                    <Link href={examTake.url({ slug: course.slug, attempt: in_progress_attempt_id })} class="block">
                        <Button class="w-full">Continue attempt</Button>
                    </Link>
                {:else}
                    <Button class="w-full" onclick={start} disabled={starting}>Start exam</Button>
                {/if}
            {/if}
        </CardContent>
    </Card>

    {#if attempts.length > 0}
        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Attempts</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="space-y-3">
                    {#each attempts as a (a.id)}
                        <li class="rounded-lg border p-3 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-medium">
                                    Attempt #{a.attempt_number}
                                </div>
                                <Badge variant={a.status === 'submitted' ? 'secondary' : 'outline'}>
                                    {a.status}
                                </Badge>
                            </div>
                            <div class="mt-1 text-xs text-muted-foreground">
                                Started: {a.started_at ?? '—'}
                                {#if a.submitted_at}
                                    <span class="px-1">·</span>
                                    Submitted: {a.submitted_at}
                                {/if}
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                {#if a.status === 'submitted'}
                                    <Badge variant="outline">
                                        Score: {a.score_points ?? 0}/{a.max_points}
                                    </Badge>
                                    {#if a.needs_manual_review}
                                        <Badge variant="outline">Pending manual review</Badge>
                                    {:else if a.passed === true}
                                        <Badge variant="secondary">Passed</Badge>
                                    {:else if a.passed === false}
                                        <Badge variant="outline">Not passed</Badge>
                                    {/if}
                                {/if}
                                {#if a.status === 'in_progress'}
                                    <Link href={examTake.url({ slug: course.slug, attempt: a.id })} class="text-primary hover:underline">
                                        Open
                                    </Link>
                                {/if}
                            </div>
                        </li>
                    {/each}
                </ul>
            </CardContent>
        </Card>
    {/if}
</div>
