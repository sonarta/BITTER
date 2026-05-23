<script module lang="ts">
    import { index as instructorHome } from '@/routes/instructor';
    import { courses as instructorCourses } from '@/routes/instructor';

    export const layout = {
        breadcrumbs: [
            { title: 'Instructor', href: instructorHome() },
            { title: 'Courses', href: instructorCourses() },
            { title: 'Exam', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router, useForm } from '@inertiajs/svelte';
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import X from 'lucide-svelte/icons/x';
    import { toast } from 'svelte-sonner';
    import { show as courseShow } from '@/routes/courses';
    import { curriculum as instructorCourseCurriculum } from '@/routes/instructor/courses';
    import { upsert as examUpsert } from '@/routes/instructor/courses/exam';
    import {
        destroy as destroyQuestionRoute,
        reorder as reorderQuestionsRoute,
        store as storeQuestionRoute,
        update as updateQuestionRoute,
    } from '@/routes/instructor/courses/exam/questions';
    import { show as showAttemptRoute } from '@/routes/instructor/courses/exam/attempts';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
    import { Textarea } from '@/components/ui/textarea';
    import { onDestroy, untrack } from 'svelte';

    type Option = { id: string; text: string; is_correct: boolean; sort_order: number };
    type Question = {
        id: string;
        type: 'single' | 'multiple' | 'true_false' | 'essay';
        prompt: string;
        points: number;
        sort_order: number;
        options: Option[];
    };
    type PublishIssue = { message: string; questionId: string | null };

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

    const form = useForm(untrack(() => ({
        title: exam?.title ?? 'Final Exam',
        description: exam?.description ?? '',
        duration_minutes: exam?.duration_minutes ?? 30,
        max_attempts: exam?.max_attempts ?? 1,
        pass_score: exam?.pass_score ?? 70,
        is_published: exam?.is_published ?? false,
    })));

    let questions = $state<Question[]>([]);
    let savingQuestions = $state<Record<string, boolean>>({});
    let deletingQuestions = $state<Record<string, boolean>>({});
    let dirtyQuestions = $state<Record<string, boolean>>({});
    let savedQuestions = $state<Record<string, boolean>>({});
    let reordering = $state(false);
    let settingsDirty = $state(false);
    let settingsSaved = $state(false);

    let settingsAutosaveTimer: ReturnType<typeof setTimeout> | null = null;
    const questionAutosaveTimers = new Map<string, ReturnType<typeof setTimeout>>();
    let highlightTimer: ReturnType<typeof setTimeout> | null = null;

    $effect(() => {
        form.title = exam?.title ?? 'Final Exam';
        form.description = exam?.description ?? '';
        form.duration_minutes = exam?.duration_minutes ?? 30;
        form.max_attempts = exam?.max_attempts ?? 1;
        form.pass_score = exam?.pass_score ?? 70;
        form.is_published = exam?.is_published ?? false;

        questions =
            exam?.questions.map((q) => ({
                ...q,
                options: q.options.map((o) => ({ ...o })),
            })) ?? [];

        dirtyQuestions = {};
        savedQuestions = {};
        settingsDirty = false;
        settingsSaved = false;
    });

    let newQuestion = $state({
        type: 'single' as Question['type'],
        prompt: '',
        points: 1,
    });

    function markDirty(questionId: string): void {
        dirtyQuestions = {
            ...dirtyQuestions,
            [questionId]: true,
        };

        if (questionAutosaveTimers.has(questionId)) {
            clearTimeout(questionAutosaveTimers.get(questionId));
        }

        questionAutosaveTimers.set(
            questionId,
            setTimeout(() => {
                if (!dirtyQuestions[questionId]) {
                    return;
                }

                if (savingQuestions[questionId] || deletingQuestions[questionId]) {
                    return;
                }

                const q = questions.find((item) => item.id === questionId);

                if (!q) {
                    return;
                }

                saveQuestion(q, 'auto');
            }, 1200),
        );
    }

    function markSaved(questionId: string): void {
        savedQuestions = {
            ...savedQuestions,
            [questionId]: true,
        };

        setTimeout(() => {
            savedQuestions = {
                ...savedQuestions,
                [questionId]: false,
            };
        }, 2000);
    }

    function markSettingsDirty(): void {
        settingsDirty = true;

        if (settingsAutosaveTimer) {
            clearTimeout(settingsAutosaveTimer);
        }

        settingsAutosaveTimer = setTimeout(() => {
            saveExam('auto');
        }, 1200);
    }

    function markSettingsSaved(): void {
        settingsDirty = false;
        settingsSaved = true;

        setTimeout(() => {
            settingsSaved = false;
        }, 2000);
    }

    function computePublishIssues(list: Question[]): PublishIssue[] {
        const issues: PublishIssue[] = [];

        if (list.length === 0) {
            issues.push({ message: 'Tambahkan minimal 1 pertanyaan.', questionId: null });
            return issues;
        }

        for (const [index, q] of list.entries()) {
            const label = `Soal #${index + 1}`;

            if (!q.prompt.trim()) {
                issues.push({ message: `${label}: prompt masih kosong.`, questionId: q.id });
            }

            if (q.type === 'essay') {
                continue;
            }

            if (q.options.length === 0) {
                issues.push({ message: `${label}: wajib punya opsi jawaban.`, questionId: q.id });
                continue;
            }

            const hasEmptyOption = q.options.some((o) => !o.text.trim());
            if (hasEmptyOption) {
                issues.push({ message: `${label}: ada opsi yang masih kosong.`, questionId: q.id });
            }

            const correctCount = q.options.filter((o) => o.is_correct).length;

            if (q.type === 'multiple') {
                if (correctCount < 1) {
                    issues.push({ message: `${label}: minimal 1 jawaban harus ditandai benar.`, questionId: q.id });
                }
            } else if (correctCount !== 1) {
                issues.push({ message: `${label}: harus punya tepat 1 jawaban benar.`, questionId: q.id });
            }
        }

        return issues;
    }

    const publishIssues = $derived(computePublishIssues(questions));

    function computeExamSummary(list: Question[], publishIssuesCount: number) {
        const essayCount = list.filter((q) => q.type === 'essay').length;
        const objectiveCount = list.length - essayCount;
        const totalPoints = list.reduce((sum, q) => sum + (Number(q.points) || 0), 0);
        const objectivePoints = list
            .filter((q) => q.type !== 'essay')
            .reduce((sum, q) => sum + (Number(q.points) || 0), 0);

        return {
            essayCount,
            objectiveCount,
            totalQuestions: list.length,
            totalPoints,
            objectivePoints,
            publishIssuesCount,
        };
    }

    const examSummary = $derived(computeExamSummary(questions, publishIssues.length));

    function normalizeExamNumbers(): void {
        form.duration_minutes = Number(form.duration_minutes) || 0;
        form.max_attempts = Number(form.max_attempts) || 0;
        form.pass_score = Number(form.pass_score) || 0;
    }

    function saveExam(mode: 'manual' | 'auto' = 'manual'): void {
        if (form.is_published && publishIssues.length > 0) {
            if (mode === 'manual') {
                toast.error('Perbaiki pertanyaan sebelum publish.');
            }

            return;
        }

        normalizeExamNumbers();

        form.post(examUpsert.url(course.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (mode === 'manual') {
                    toast.success('Exam saved.');
                } else {
                    markSettingsSaved();
                }
            },
        });
    }

    let bulkSaving = $state(false);
    let bulkSavePending = $state(0);
    let bulkSaveErrors = $state(0);
    let highlightedQuestionId = $state<string | null>(null);

    const dirtyCount = $derived(questions.filter((q) => Boolean(dirtyQuestions[q.id])).length);

    function scrollToQuestion(questionId: string | null): void {
        if (!questionId || typeof document === 'undefined') {
            return;
        }

        const element = document.getElementById(`question-${questionId}`);

        if (!element) {
            return;
        }

        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        highlightedQuestionId = questionId;

        if (highlightTimer) {
            clearTimeout(highlightTimer);
        }

        highlightTimer = setTimeout(() => {
            highlightedQuestionId = null;
        }, 2000);
    }

    function saveAllDirtyQuestions(): void {
        if (bulkSaving) {
            return;
        }

        const dirtyQuestionsList = questions.filter((q) => Boolean(dirtyQuestions[q.id]));

        if (dirtyQuestionsList.length === 0) {
            toast.info('Tidak ada soal yang perlu disimpan.');
            return;
        }

        bulkSaving = true;
        bulkSavePending = dirtyQuestionsList.length;
        bulkSaveErrors = 0;

        const done = (success: boolean) => {
            bulkSavePending = Math.max(0, bulkSavePending - 1);
            if (!success) {
                bulkSaveErrors = bulkSaveErrors + 1;
            }

            if (bulkSavePending === 0) {
                bulkSaving = false;
                if (bulkSaveErrors === 0) {
                    if (settingsDirty && !form.processing) {
                        saveExam('auto');
                    }

                    toast.success('Semua tersimpan.');
                } else {
                    toast.error(`${bulkSaveErrors} soal gagal disimpan.`);
                }
            }
        };

        for (const q of dirtyQuestionsList) {
            if (savingQuestions[q.id] || deletingQuestions[q.id]) {
                done(true);
                continue;
            }

            saveQuestion(q, 'auto', done);
        }
    }

    function addQuestion(): void {
        newQuestion.prompt = newQuestion.prompt.trim();
        newQuestion.points = Number(newQuestion.points) || 0;

        router.post(storeQuestionRoute.url(course.id), newQuestion, {
            preserveScroll: true,
            onSuccess: () => {
                newQuestion.prompt = '';
                newQuestion.points = 1;
                newQuestion.type = 'single';
            },
        });
    }

    function normalizeCorrectOptions(q: Question): void {
        if (q.type === 'essay') {
            q.options = [];
            return;
        }

        const correctIndexes = q.options
            .map((o, idx) => (o.is_correct ? idx : -1))
            .filter((idx) => idx >= 0);

        if (q.type === 'multiple') {
            return;
        }

        if (q.options.length === 0) {
            return;
        }

        if (correctIndexes.length === 1) {
            return;
        }

        const keep = correctIndexes[0] ?? 0;

        q.options = q.options.map((opt, idx) => ({
            ...opt,
            is_correct: idx === keep,
        }));
    }

    function handleQuestionTypeChange(q: Question): void {
        if (q.type === 'essay') {
            q.options = [];
            return;
        }

        if (q.type === 'true_false') {
            if (q.options.length === 0) {
                q.options = [
                    { id: '', text: 'True', is_correct: false, sort_order: 0 },
                    { id: '', text: 'False', is_correct: false, sort_order: 1 },
                ];
            }
        }

        normalizeCorrectOptions(q);
    }

    function saveQuestion(
        q: Question,
        mode: 'manual' | 'auto' = 'manual',
        onDone: ((success: boolean) => void) | null = null,
    ): void {
        if (!dirtyQuestions[q.id] || savingQuestions[q.id]) {
            return;
        }

        q.points = Number(q.points) || 0;
        normalizeCorrectOptions(q);

        let hasError = false;

        router.put(updateQuestionRoute.url({ course: course.id, question: q.id }), {
            type: q.type,
            prompt: q.prompt,
            points: q.points,
            options: q.options.map((o, idx) => ({
                id: o.id,
                text: o.text,
                is_correct: o.is_correct,
                sort_order: idx,
            })),
        }, {
            preserveScroll: true,
            onStart: () => {
                savingQuestions = { ...savingQuestions, [q.id]: true };
            },
            onSuccess: () => {
                dirtyQuestions = { ...dirtyQuestions, [q.id]: false };
                markSaved(q.id);

                if (mode === 'manual') {
                    toast.success('Question saved.');
                }
            },
            onError: () => {
                hasError = true;
                if (mode === 'manual') {
                    toast.error('Gagal menyimpan soal.');
                }
            },
            onFinish: () => {
                savingQuestions = { ...savingQuestions, [q.id]: false };
                onDone?.(!hasError);
            },
        });
    }

    function deleteQuestion(q: Question): void {
        if (!confirm('Delete this question?')) {
            return;
        }

        router.delete(destroyQuestionRoute.url({ course: course.id, question: q.id }), {
            preserveScroll: true,
            onStart: () => {
                deletingQuestions = { ...deletingQuestions, [q.id]: true };
            },
            onFinish: () => {
                deletingQuestions = { ...deletingQuestions, [q.id]: false };
            },
        });
    }

    function addOption(q: Question): void {
        if (q.type === 'essay' || q.type === 'true_false') {
            return;
        }

        q.options = [
            ...q.options,
            { id: '', text: '', is_correct: false, sort_order: q.options.length },
        ];
        markDirty(q.id);
    }

    function removeOption(q: Question, index: number): void {
        if (q.type === 'true_false') {
            return;
        }

        q.options = q.options.filter((_, i) => i !== index);
        normalizeCorrectOptions(q);
        markDirty(q.id);
    }

    function setSingleCorrectOption(q: Question, optionIndex: number): void {
        q.options = q.options.map((opt, idx) => ({
            ...opt,
            is_correct: idx === optionIndex,
        }));
        markDirty(q.id);
    }

    function moveQuestion(qid: string, direction: -1 | 1): void {
        const index = questions.findIndex((q) => q.id === qid);
        const nextIndex = index + direction;
        if (index < 0 || nextIndex < 0 || nextIndex >= questions.length) return;

        const copy = [...questions];
        const [item] = copy.splice(index, 1);
        copy.splice(nextIndex, 0, item);
        questions = copy;

        reordering = true;
        router.post(reorderQuestionsRoute.url(course.id), {
            order: questions.map((q) => q.id),
        }, {
            preserveScroll: true,
            onFinish: () => {
                reordering = false;
            },
        });
    }

    function formatDateTime(value: string | null): string {
        if (!value) {
            return '—';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(date);
    }

    function getAttemptStatusLabel(attempt: { status: string; needs_manual_review: boolean; passed: boolean | null }): string {
        if (attempt.status === 'in_progress') {
            return 'Sedang dikerjakan';
        }

        if (attempt.status === 'expired') {
            return 'Waktu habis';
        }

        if (attempt.status === 'submitted') {
            if (attempt.needs_manual_review) {
                return 'Perlu review';
            }

            if (attempt.passed === true) {
                return 'Lulus';
            }

            if (attempt.passed === false) {
                return 'Tidak lulus';
            }

            return 'Terkirim';
        }

        return attempt.status;
    }

    function getAttemptStatusVariant(attempt: { status: string; needs_manual_review: boolean; passed: boolean | null }): 'default' | 'secondary' | 'destructive' | 'outline' {
        if (attempt.status === 'submitted' && attempt.needs_manual_review) {
            return 'destructive';
        }

        if (attempt.status === 'submitted' && attempt.passed === true) {
            return 'secondary';
        }

        return 'outline';
    }

    onDestroy(() => {
        if (settingsAutosaveTimer) {
            clearTimeout(settingsAutosaveTimer);
        }

        for (const timer of questionAutosaveTimers.values()) {
            clearTimeout(timer);
        }

        questionAutosaveTimers.clear();

        if (highlightTimer) {
            clearTimeout(highlightTimer);
        }
    });
</script>

<AppHead title={`Exam Editor · ${course.title}`} />

<div class="mx-auto w-full max-w-5xl px-4 py-8 space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{course.title}</h1>
            <p class="text-sm text-muted-foreground">Final exam settings & questions</p>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <Badge variant="outline">{examSummary.totalQuestions} questions</Badge>
                <Badge variant="outline">{examSummary.objectiveCount} objective</Badge>
                <Badge variant="outline">{examSummary.essayCount} essay</Badge>
                <Badge variant="outline">{examSummary.totalPoints} total points</Badge>
                {#if examSummary.publishIssuesCount > 0}
                    <Badge variant="destructive">{examSummary.publishIssuesCount} publish issue(s)</Badge>
                {/if}
            </div>
        </div>
        <div class="flex items-center gap-2">
            <Link href={instructorCourseCurriculum(course.id)}>
                <Button variant="outline">Curriculum</Button>
            </Link>
            <Link href={courseShow(course.slug)}>
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
                    <Label for="exam-title">Title</Label>
                    <Input id="exam-title" bind:value={form.title} oninput={markSettingsDirty} />
                </div>
                <div class="space-y-1.5">
                    <Label for="exam-duration">Duration (minutes)</Label>
                    <Input id="exam-duration" type="number" bind:value={form.duration_minutes} oninput={markSettingsDirty} />
                </div>
                <div class="space-y-1.5">
                    <Label for="exam-max-attempts">Max attempts</Label>
                    <Input id="exam-max-attempts" type="number" bind:value={form.max_attempts} oninput={markSettingsDirty} />
                </div>
                <div class="space-y-1.5">
                    <Label for="exam-pass-score">Pass score (%)</Label>
                    <Input id="exam-pass-score" type="number" bind:value={form.pass_score} oninput={markSettingsDirty} />
                </div>
            </div>
            <div class="space-y-1.5">
                <Label for="exam-description">Description</Label>
                <Textarea id="exam-description" rows={3} bind:value={form.description} oninput={markSettingsDirty} />
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" bind:checked={form.is_published} class="rounded border-input" onchange={markSettingsDirty} />
                Publish exam (students can take it)
            </label>
            {#if form.is_published && publishIssues.length > 0}
                <div class="rounded-md border border-destructive/30 bg-destructive/5 p-3">
                    <p class="text-sm font-medium text-destructive">Tidak bisa publish dulu</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-muted-foreground">
                        {#each publishIssues as issue (issue.message + (issue.questionId ?? ''))}
                            <li class="flex flex-wrap items-center justify-between gap-2">
                                <span>{issue.message}</span>
                                {#if issue.questionId}
                                    <Button
                                        variant="link"
                                        size="sm"
                                        class="h-auto p-0 text-xs"
                                        onclick={() => scrollToQuestion(issue.questionId)}
                                    >
                                        Lihat soal
                                    </Button>
                                {/if}
                            </li>
                        {/each}
                    </ul>
                </div>
            {/if}
            <InputError message={form.errors.is_published} />
            <div class="flex items-center gap-2">
                {#if settingsSaved}
                    <Badge variant="secondary">Saved</Badge>
                {:else if settingsDirty}
                    <Badge variant="outline">Unsaved</Badge>
                {/if}
                <Button onclick={() => saveExam('manual')} disabled={form.processing}>Save exam</Button>
            </div>
        </CardContent>
    </Card>

    <Card>
        <CardHeader class="flex flex-row items-center justify-between gap-2">
            <CardTitle>Questions</CardTitle>
            <div class="flex items-center gap-2">
                {#if bulkSaving}
                    <Badge variant="outline">Saving {bulkSavePending}…</Badge>
                {:else if dirtyCount > 0}
                    <Badge variant="outline">{dirtyCount} unsaved</Badge>
                {/if}
                <Button
                    variant="outline"
                    size="sm"
                    onclick={saveAllDirtyQuestions}
                    disabled={bulkSaving || dirtyCount === 0}
                >
                    Save all
                </Button>
            </div>
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
                    <Button onclick={addQuestion} class="gap-1" disabled={!newQuestion.prompt.trim()}>
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
                        <div
                            id={`question-${q.id}`}
                            class={`rounded-lg border p-4 space-y-3 scroll-mt-24 ${highlightedQuestionId === q.id ? 'ring-2 ring-primary/60' : ''}`}
                        >
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
                                        disabled={index === 0 || reordering}
                                    >
                                        Up
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onclick={() => moveQuestion(q.id, 1)}
                                        disabled={index === questions.length - 1 || reordering}
                                    >
                                        Down
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        aria-label="Delete question"
                                        onclick={() => deleteQuestion(q)}
                                        disabled={deletingQuestions[q.id] || savingQuestions[q.id]}
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div class="space-y-1.5 md:col-span-1">
                                    <Label for={`q-${q.id}-type`}>Type</Label>
                                    <select
                                        id={`q-${q.id}-type`}
                                        bind:value={q.type}
                                        onchange={() => {
                                            handleQuestionTypeChange(q);
                                            markDirty(q.id);
                                        }}
                                        class="h-9 w-full rounded-md border border-input bg-transparent px-2 text-sm"
                                    >
                                        <option value="single">Single choice</option>
                                        <option value="multiple">Multiple select</option>
                                        <option value="true_false">True / False</option>
                                        <option value="essay">Essay</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5 md:col-span-2">
                                    <Label for={`q-${q.id}-prompt`}>Prompt</Label>
                                    <Textarea
                                        id={`q-${q.id}-prompt`}
                                        rows={2}
                                        bind:value={q.prompt}
                                        oninput={() => markDirty(q.id)}
                                    />
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-28 space-y-1.5">
                                    <Label for={`q-${q.id}-points`}>Points</Label>
                                    <Input
                                        id={`q-${q.id}-points`}
                                        type="number"
                                        bind:value={q.points}
                                        oninput={() => markDirty(q.id)}
                                    />
                                </div>
                                <div class="flex-1"></div>
                                {#if savedQuestions[q.id]}
                                    <Badge variant="secondary">Saved</Badge>
                                {:else if dirtyQuestions[q.id]}
                                    <Badge variant="outline">Unsaved</Badge>
                                {/if}
                                <Button
                                    size="sm"
                                    onclick={() => saveQuestion(q, 'manual')}
                                    disabled={!dirtyQuestions[q.id] || savingQuestions[q.id]}
                                >
                                    {savingQuestions[q.id] ? 'Saving…' : 'Save'}
                                </Button>
                            </div>

                            {#if q.type !== 'essay'}
                                <Separator />
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <Label>Options</Label>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="gap-1"
                                            onclick={() => addOption(q)}
                                            disabled={q.type === 'true_false'}
                                        >
                                            <Plus class="size-3.5" />
                                            Add option
                                        </Button>
                                    </div>
                                    {#each q.options as opt, oi (oi)}
                                        <div class="flex items-start gap-2">
                                            {#if q.type === 'multiple'}
                                                <input
                                                    type="checkbox"
                                                    bind:checked={opt.is_correct}
                                                    onchange={() => markDirty(q.id)}
                                                    class="mt-2 rounded border-input"
                                                />
                                            {:else}
                                                <input
                                                    type="radio"
                                                    name={`correct-${q.id}`}
                                                    checked={opt.is_correct}
                                                    onchange={() => setSingleCorrectOption(q, oi)}
                                                    class="mt-2 rounded border-input"
                                                />
                                            {/if}
                                            <Input
                                                bind:value={opt.text}
                                                placeholder="Option text"
                                                class="flex-1"
                                                oninput={() => markDirty(q.id)}
                                            />
                                            {#if q.type !== 'true_false'}
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label="Remove option"
                                                    onclick={() => removeOption(q, oi)}
                                                >
                                                    <X class="size-4" />
                                                </Button>
                                            {/if}
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
                                <Badge variant={getAttemptStatusVariant(a)}>
                                    {getAttemptStatusLabel(a)}
                                </Badge>
                            </div>
                            <div class="mt-1 text-xs text-muted-foreground">
                                Started: {formatDateTime(a.started_at)}
                                {#if a.submitted_at}
                                    <span class="px-1">·</span>
                                    Submitted: {formatDateTime(a.submitted_at)}
                                {/if}
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                {#if a.status === 'submitted'}
                                    <Badge variant="outline">
                                        Score: {a.score_points ?? 0}/{a.max_points}
                                    </Badge>
                                {/if}
                                <Link
                                    href={showAttemptRoute.url({ course: course.id, attempt: a.id })}
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
