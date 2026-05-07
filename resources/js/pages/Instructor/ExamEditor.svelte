<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Instructor', href: '/instructor' },
            { title: 'Courses', href: '/instructor/courses' },
            { title: 'Exam', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
    import { Textarea } from '@/components/ui/textarea';

    type Option = { id: string; text: string; is_correct: boolean; sort_order: number };
    type Question = {
        id: string;
        type: 'single' | 'multiple' | 'true_false' | 'essay';
        prompt: string;
        points: number;
        sort_order: number;
        options: Option[];
    };

    let { course, exam = null, attempts = [] }: {
        course: { id: string; title: string; slug: string; status: string };
        exam: {
            id: string;
            title: string;
            description: string;
            duration_minutes: number;
            max_attempts: number;
            pass_score: number;
            is_published: boolean;
            questions: Question[];
        } | null;
        attempts: {
            id: string;
            user: { id: number; name: string };
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

    let examForm = $state({
        title: 'Final Exam',
        description: '',
        duration_minutes: 30,
        max_attempts: 1,
        pass_score: 70,
        is_published: false,
    });

    let questions = $state<Question[]>([]);

    $effect(() => {
        examForm = {
            title: exam?.title ?? 'Final Exam',
            description: exam?.description ?? '',
            duration_minutes: exam?.duration_minutes ?? 30,
            max_attempts: exam?.max_attempts ?? 1,
            pass_score: exam?.pass_score ?? 70,
            is_published: exam?.is_published ?? false,
        };

        questions = exam?.questions ?? [];
    });

    let newQuestion = $state({
        type: 'single' as Question['type'],
        prompt: '',
        points: 1,
    });

    function saveExam(): void {
        router.post(`/instructor/courses/${course.id}/exam`, examForm);
    }

    function addQuestion(): void {
        router.post(`/instructor/courses/${course.id}/exam/questions`, newQuestion, {
            onSuccess: () => {
                newQuestion.prompt = '';
                newQuestion.points = 1;
                newQuestion.type = 'single';
            },
        });
    }

    function saveQuestion(q: Question): void {
        router.put(`/instructor/courses/${course.id}/exam/questions/${q.id}`, {
            type: q.type,
            prompt: q.prompt,
            points: q.points,
            options: q.options.map((o, idx) => ({
                id: o.id,
                text: o.text,
                is_correct: o.is_correct,
                sort_order: idx,
            })),
        });
    }

    function deleteQuestion(q: Question): void {
        router.delete(`/instructor/courses/${course.id}/exam/questions/${q.id}`);
    }

    function addOption(q: Question): void {
        q.options = [
            ...q.options,
            { id: '', text: '', is_correct: false, sort_order: q.options.length },
        ];
    }

    function moveQuestion(qid: string, direction: -1 | 1): void {
        const index = questions.findIndex((q) => q.id === qid);
        const nextIndex = index + direction;
        if (index < 0 || nextIndex < 0 || nextIndex >= questions.length) return;

        const copy = [...questions];
        const [item] = copy.splice(index, 1);
        copy.splice(nextIndex, 0, item);
        questions = copy;

        router.post(`/instructor/courses/${course.id}/exam/questions/reorder`, {
            order: questions.map((q) => q.id),
        });
    }
</script>

<AppHead title={`Exam Editor · ${course.title}`} />

<div class="mx-auto w-full max-w-5xl px-4 py-8 space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{course.title}</h1>
            <p class="text-sm text-muted-foreground">Final exam settings & questions</p>
        </div>
        <div class="flex items-center gap-2">
            <Link href={`/instructor/courses/${course.id}/curriculum`}>
                <Button variant="outline">Curriculum</Button>
            </Link>
            <Link href={`/courses/${course.slug}`}>
                <Button variant="outline">Public page</Button>
            </Link>
        </div>
    </div>

    <Card>
        <CardHeader>
            <CardTitle>Exam settings</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-1.5">
                    <Label>Title</Label>
                    <Input bind:value={examForm.title} />
                </div>
                <div class="space-y-1.5">
                    <Label>Duration (minutes)</Label>
                    <Input type="number" bind:value={examForm.duration_minutes} />
                </div>
                <div class="space-y-1.5">
                    <Label>Max attempts</Label>
                    <Input type="number" bind:value={examForm.max_attempts} />
                </div>
                <div class="space-y-1.5">
                    <Label>Pass score (%)</Label>
                    <Input type="number" bind:value={examForm.pass_score} />
                </div>
            </div>
            <div class="space-y-1.5">
                <Label>Description</Label>
                <Textarea rows={3} bind:value={examForm.description} />
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" bind:checked={examForm.is_published} class="rounded border-input" />
                Publish exam (students can take it)
            </label>
            <Button onclick={saveExam}>Save exam</Button>
        </CardContent>
    </Card>

    <Card>
        <CardHeader>
            <CardTitle>Questions</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
            <div class="rounded-lg border p-4 space-y-3">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div class="space-y-1.5 md:col-span-1">
                        <Label>Type</Label>
                        <select
                            bind:value={newQuestion.type}
                            class="h-9 w-full rounded-md border border-input bg-transparent px-2 text-sm"
                        >
                            <option value="single">Single choice</option>
                            <option value="multiple">Multiple select</option>
                            <option value="true_false">True / False</option>
                            <option value="essay">Essay</option>
                        </select>
                    </div>
                    <div class="space-y-1.5 md:col-span-2">
                        <Label>Prompt</Label>
                        <Textarea rows={2} bind:value={newQuestion.prompt} placeholder="Question text..." />
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-28 space-y-1.5">
                        <Label>Points</Label>
                        <Input type="number" bind:value={newQuestion.points} />
                    </div>
                    <div class="flex-1"></div>
                    <Button onclick={addQuestion} class="gap-1">
                        <Plus class="size-4" />
                        Add question
                    </Button>
                </div>
            </div>

            {#if questions.length === 0}
                <p class="text-sm text-muted-foreground">
                    Belum ada pertanyaan.
                </p>
            {:else}
                <div class="space-y-4">
                    {#each questions as q, index (q.id)}
                        <div class="rounded-lg border p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <Badge variant="outline">#{index + 1}</Badge>
                                        <Badge variant="outline">{q.type}</Badge>
                                        <Badge variant="outline">{q.points} pt</Badge>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onclick={() => moveQuestion(q.id, -1)}
                                        disabled={index === 0}
                                    >
                                        Up
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onclick={() => moveQuestion(q.id, 1)}
                                        disabled={index === questions.length - 1}
                                    >
                                        Down
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        onclick={() => deleteQuestion(q)}
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div class="space-y-1.5 md:col-span-1">
                                    <Label>Type</Label>
                                    <select
                                        bind:value={q.type}
                                        class="h-9 w-full rounded-md border border-input bg-transparent px-2 text-sm"
                                    >
                                        <option value="single">Single choice</option>
                                        <option value="multiple">Multiple select</option>
                                        <option value="true_false">True / False</option>
                                        <option value="essay">Essay</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5 md:col-span-2">
                                    <Label>Prompt</Label>
                                    <Textarea rows={2} bind:value={q.prompt} />
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-28 space-y-1.5">
                                    <Label>Points</Label>
                                    <Input type="number" bind:value={q.points} />
                                </div>
                                <div class="flex-1"></div>
                                <Button size="sm" onclick={() => saveQuestion(q)}>Save</Button>
                            </div>

                            {#if q.type !== 'essay'}
                                <Separator />
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <Label>Options</Label>
                                        <Button variant="outline" size="sm" class="gap-1" onclick={() => addOption(q)}>
                                            <Plus class="size-3.5" />
                                            Add option
                                        </Button>
                                    </div>
                                    {#each q.options as opt, oi (oi)}
                                        <div class="flex items-start gap-2">
                                            <input type="checkbox" bind:checked={opt.is_correct} class="mt-2 rounded border-input" />
                                            <Input bind:value={opt.text} placeholder="Option text" class="flex-1" />
                                        </div>
                                    {/each}
                                    <p class="text-xs text-muted-foreground">
                                        Untuk type single/true_false, pastikan hanya 1 jawaban yang benar.
                                    </p>
                                </div>
                            {/if}
                        </div>
                    {/each}
                </div>
            {/if}
        </CardContent>
    </Card>

    <Card>
        <CardHeader>
            <CardTitle>Recent attempts</CardTitle>
        </CardHeader>
        <CardContent>
            {#if attempts.length === 0}
                <p class="text-sm text-muted-foreground">Belum ada attempt.</p>
            {:else}
                <ul class="space-y-3">
                    {#each attempts as a (a.id)}
                        <li class="rounded-lg border p-3 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-medium">{a.user.name} · Attempt #{a.attempt_number}</div>
                                <Badge variant="outline">{a.status}</Badge>
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
                                        <Badge variant="outline">Needs manual review</Badge>
                                    {/if}
                                {/if}
                                <Link
                                    href={`/instructor/courses/${course.id}/exam/attempts/${a.id}`}
                                    class="text-primary hover:underline"
                                >
                                    View
                                </Link>
                            </div>
                        </li>
                    {/each}
                </ul>
            {/if}
        </CardContent>
    </Card>
</div>
