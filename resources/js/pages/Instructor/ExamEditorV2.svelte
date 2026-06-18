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
    type Readiness = {
        blockers: { message: string; question_id: string | null }[];
        warnings: { message: string; question_id: string | null }[];
        summary: {
            total_questions: number;
            objective_count: number;
            essay_count: number;
            total_points: number;
            objective_points: number;
            blockers_count: number;
            warnings_count: number;
            can_publish: boolean;
        };
    };
    type EditorTab = 'overview' | 'builder' | 'preview' | 'attempts';

    let {
        course,
        exam = null,
        attempts = [],
        readiness,
        ui,
    }: {
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
        readiness: Readiness;
        ui: {
            active_tab: EditorTab;
            features: {
                exam_editor_v2: boolean;
            };
        };
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
    let activeTab = $state<EditorTab>('overview');

    let settingsAutosaveTimer: ReturnType<typeof setTimeout> | null = null;
    const questionAutosaveTimers = new Map<string, ReturnType<typeof setTimeout>>();
    let highlightTimer: ReturnType<typeof setTimeout> | null = null;

    const tabs: { id: EditorTab; label: string }[] = [
        { id: 'overview', label: 'Overview' },
        { id: 'builder', label: 'Builder' },
        { id: 'preview', label: 'Preview' },
        { id: 'attempts', label: 'Attempts' },
    ];

    $effect(() => {
        activeTab = ui?.active_tab ?? 'overview';
        form.title = exam?.title ?? 'Final Exam';
        form.description = exam?.description ?? '';
        form.duration_minutes = exam?.duration_minutes ?? 30;
        form.max_attempts = exam?.max_attempts ?? 1;
        form.pass_score = exam?.pass_score ?? 70;
        form.is_published = exam?.is_published ?? false;

        questions =
            exam?.questions.map((question) => ({
                ...question,
                options: question.options.map((option) => ({ ...option })),
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

    function setActiveTab(tab: EditorTab): void {
        activeTab = tab;
    }

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

                const question = questions.find((item) => item.id === questionId);

                if (!question) {
                    return;
                }

                saveQuestion(question, 'auto');
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

        for (const [index, question] of list.entries()) {
            const label = `Soal #${index + 1}`;

            if (!question.prompt.trim()) {
                issues.push({ message: `${label}: prompt masih kosong.`, questionId: question.id });
            }

            if (question.type === 'essay') {
                continue;
            }

            if (question.options.length === 0) {
                issues.push({ message: `${label}: wajib punya opsi jawaban.`, questionId: question.id });
                continue;
            }

            const hasEmptyOption = question.options.some((option) => !option.text.trim());
            if (hasEmptyOption) {
                issues.push({ message: `${label}: ada opsi yang masih kosong.`, questionId: question.id });
            }

            const correctCount = question.options.filter((option) => option.is_correct).length;

            if (question.type === 'multiple') {
                if (correctCount < 1) {
                    issues.push({ message: `${label}: minimal 1 jawaban harus ditandai benar.`, questionId: question.id });
                }
            } else if (correctCount !== 1) {
                issues.push({ message: `${label}: harus punya tepat 1 jawaban benar.`, questionId: question.id });
            }
        }

        return issues;
    }

    const publishIssues = $derived(computePublishIssues(questions));

    function computeExamSummary(list: Question[], issuesCount: number) {
        const essayCount = list.filter((question) => question.type === 'essay').length;
        const objectiveCount = list.length - essayCount;
        const totalPoints = list.reduce((sum, question) => sum + (Number(question.points) || 0), 0);
        const objectivePoints = list
            .filter((question) => question.type !== 'essay')
            .reduce((sum, question) => sum + (Number(question.points) || 0), 0);

        return {
            essayCount,
            objectiveCount,
            totalQuestions: list.length,
            totalPoints,
            objectivePoints,
            publishIssuesCount: issuesCount,
        };
    }

    const examSummary = $derived(computeExamSummary(questions, publishIssues.length));
    const effectiveWarnings = $derived(readiness?.warnings ?? []);

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
                    toast.success(form.is_published ? 'Exam published.' : 'Draft saved.');
                } else {
                    markSettingsSaved();
                }
            },
        });
    }

    function saveDraft(): void {
        form.is_published = false;
        saveExam('manual');
    }

    function publishExam(): void {
        form.is_published = true;
        setActiveTab('preview');
        saveExam('manual');
    }

    let bulkSaving = $state(false);
    let bulkSavePending = $state(0);
    let bulkSaveErrors = $state(0);
    let highlightedQuestionId = $state<string | null>(null);

    const dirtyCount = $derived(questions.filter((question) => Boolean(dirtyQuestions[question.id])).length);
    const isSavingAnyQuestion = $derived(Object.values(savingQuestions).some(Boolean));
    const globalSaveLabel = $derived.by(() => {
        if (form.processing || bulkSaving || isSavingAnyQuestion) {
            return 'Saving...';
        }

        if (settingsDirty || dirtyCount > 0) {
            return 'Unsaved changes';
        }

        if (settingsSaved) {
            return 'Saved';
        }

        return form.is_published ? 'Published' : 'Draft';
    });

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

    function goToQuestion(questionId: string | null): void {
        setActiveTab('builder');

        setTimeout(() => {
            scrollToQuestion(questionId);
        }, 0);
    }

    function saveAllDirtyQuestions(): void {
        if (bulkSaving) {
            return;
        }

        const dirtyQuestionsList = questions.filter((question) => Boolean(dirtyQuestions[question.id]));

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

        for (const question of dirtyQuestionsList) {
            if (savingQuestions[question.id] || deletingQuestions[question.id]) {
                done(true);
                continue;
            }

            saveQuestion(question, 'auto', done);
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
                setActiveTab('builder');
            },
        });
    }

    function normalizeCorrectOptions(question: Question): void {
        if (question.type === 'essay') {
            question.options = [];
            return;
        }

        const correctIndexes = question.options
            .map((option, index) => (option.is_correct ? index : -1))
            .filter((index) => index >= 0);

        if (question.type === 'multiple') {
            return;
        }

        if (question.options.length === 0) {
            return;
        }

        if (correctIndexes.length === 1) {
            return;
        }

        const keep = correctIndexes[0] ?? 0;

        question.options = question.options.map((option, index) => ({
            ...option,
            is_correct: index === keep,
        }));
    }

    function handleQuestionTypeChange(question: Question): void {
        if (question.type === 'essay') {
            question.options = [];
            return;
        }

        if (question.type === 'true_false' && question.options.length === 0) {
            question.options = [
                { id: '', text: 'True', is_correct: false, sort_order: 0 },
                { id: '', text: 'False', is_correct: false, sort_order: 1 },
            ];
        }

        normalizeCorrectOptions(question);
    }

    function saveQuestion(
        question: Question,
        mode: 'manual' | 'auto' = 'manual',
        onDone: ((success: boolean) => void) | null = null,
    ): void {
        if (!dirtyQuestions[question.id] || savingQuestions[question.id]) {
            return;
        }

        question.points = Number(question.points) || 0;
        normalizeCorrectOptions(question);

        let hasError = false;

        router.put(updateQuestionRoute.url({ course: course.id, question: question.id }), {
            type: question.type,
            prompt: question.prompt,
            points: question.points,
            options: question.options.map((option, index) => ({
                id: option.id,
                text: option.text,
                is_correct: option.is_correct,
                sort_order: index,
            })),
        }, {
            preserveScroll: true,
            onStart: () => {
                savingQuestions = { ...savingQuestions, [question.id]: true };
            },
            onSuccess: () => {
                dirtyQuestions = { ...dirtyQuestions, [question.id]: false };
                markSaved(question.id);

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
                savingQuestions = { ...savingQuestions, [question.id]: false };
                onDone?.(!hasError);
            },
        });
    }

    function deleteQuestion(question: Question): void {
        if (!confirm('Delete this question?')) {
            return;
        }

        router.delete(destroyQuestionRoute.url({ course: course.id, question: question.id }), {
            preserveScroll: true,
            onStart: () => {
                deletingQuestions = { ...deletingQuestions, [question.id]: true };
            },
            onFinish: () => {
                deletingQuestions = { ...deletingQuestions, [question.id]: false };
            },
        });
    }

    function addOption(question: Question): void {
        if (question.type === 'essay' || question.type === 'true_false') {
            return;
        }

        question.options = [
            ...question.options,
            { id: '', text: '', is_correct: false, sort_order: question.options.length },
        ];
        markDirty(question.id);
    }

    function removeOption(question: Question, index: number): void {
        if (question.type === 'true_false') {
            return;
        }

        question.options = question.options.filter((_, optionIndex) => optionIndex !== index);
        normalizeCorrectOptions(question);
        markDirty(question.id);
    }

    function setSingleCorrectOption(question: Question, optionIndex: number): void {
        question.options = question.options.map((option, index) => ({
            ...option,
            is_correct: index === optionIndex,
        }));
        markDirty(question.id);
    }

    function moveQuestion(questionId: string, direction: -1 | 1): void {
        const index = questions.findIndex((question) => question.id === questionId);
        const nextIndex = index + direction;
        if (index < 0 || nextIndex < 0 || nextIndex >= questions.length) {
            return;
        }

        const copy = [...questions];
        const [item] = copy.splice(index, 1);
        copy.splice(nextIndex, 0, item);
        questions = copy;

        reordering = true;
        router.post(reorderQuestionsRoute.url(course.id), {
            order: questions.map((question) => question.id),
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

<div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold">{course.title}</h1>
                <Badge variant={form.is_published ? 'secondary' : 'outline'}>
                    {form.is_published ? 'Published' : 'Draft'}
                </Badge>
                <Badge variant={settingsDirty || dirtyCount > 0 ? 'outline' : 'secondary'}>
                    {globalSaveLabel}
                </Badge>
            </div>
            <p class="text-sm text-muted-foreground">Workspace baru untuk mengelola setup, builder, preview, dan attempts secara terpisah.</p>
            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <Badge variant="outline">{examSummary.totalQuestions} questions</Badge>
                <Badge variant="outline">{examSummary.objectiveCount} objective</Badge>
                <Badge variant="outline">{examSummary.essayCount} essay</Badge>
                <Badge variant="outline">{examSummary.totalPoints} total points</Badge>
                {#if publishIssues.length > 0}
                    <Badge variant="destructive">{publishIssues.length} publish issue(s)</Badge>
                {/if}
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Button variant="outline" onclick={() => setActiveTab('preview')}>Preview</Button>
            <Button variant="outline" onclick={saveDraft} disabled={form.processing}>Save draft</Button>
            <Button onclick={publishExam} disabled={form.processing || publishIssues.length > 0}>Publish exam</Button>
            <Link href={instructorCourseCurriculum(course.id)}>
                <Button variant="outline">Curriculum</Button>
            </Link>
            <Link href={courseShow(course.slug)}>
                <Button variant="outline">Public page</Button>
            </Link>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 border-b border-border pb-2">
        {#each tabs as tab (tab.id)}
            <Button
                variant={activeTab === tab.id ? 'default' : 'ghost'}
                size="sm"
                onclick={() => setActiveTab(tab.id)}
            >
                {tab.label}
            </Button>
        {/each}
    </div>

    {#if activeTab === 'overview'}
        <Card>
            <CardHeader>
                <CardTitle>Overview</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="exam-title-v2">Title</Label>
                        <Input id="exam-title-v2" bind:value={form.title} oninput={markSettingsDirty} />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="exam-duration-v2">Duration (minutes)</Label>
                        <Input id="exam-duration-v2" type="number" bind:value={form.duration_minutes} oninput={markSettingsDirty} />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="exam-max-attempts-v2">Max attempts</Label>
                        <Input id="exam-max-attempts-v2" type="number" bind:value={form.max_attempts} oninput={markSettingsDirty} />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="exam-pass-score-v2">Pass score (%)</Label>
                        <Input id="exam-pass-score-v2" type="number" bind:value={form.pass_score} oninput={markSettingsDirty} />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label for="exam-description-v2">Description</Label>
                    <Textarea id="exam-description-v2" rows={4} bind:value={form.description} oninput={markSettingsDirty} />
                </div>

                <div class="rounded-lg border border-border/70 bg-muted/20 p-4 text-sm">
                    <p class="font-medium">Status exam</p>
                    <p class="mt-1 text-muted-foreground">
                        {form.is_published
                            ? 'Exam sudah published dan bisa diakses student sesuai aturan course.'
                            : 'Exam masih draft. Publish dilakukan dari tab Preview setelah semua blocker selesai.'}
                    </p>
                </div>

                <InputError message={form.errors.is_published} />

                <div class="flex items-center gap-2">
                    {#if settingsSaved}
                        <Badge variant="secondary">Saved</Badge>
                    {:else if settingsDirty}
                        <Badge variant="outline">Unsaved</Badge>
                    {/if}
                    <Button onclick={saveDraft} disabled={form.processing}>Save draft</Button>
                    <Button variant="outline" onclick={() => setActiveTab('builder')}>Continue to builder</Button>
                </div>
            </CardContent>
        </Card>
    {/if}

    {#if activeTab === 'builder'}
        <Card>
            <CardHeader class="flex flex-row items-center justify-between gap-2">
                <CardTitle>Builder</CardTitle>
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
                    <p class="text-sm text-muted-foreground">Belum ada pertanyaan.</p>
                {:else}
                    <div class="space-y-4">
                        {#each questions as question, index (question.id)}
                            <div
                                id={`question-${question.id}`}
                                class={`rounded-lg border p-4 space-y-3 scroll-mt-24 ${highlightedQuestionId === question.id ? 'ring-2 ring-primary/60' : ''}`}
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <Badge variant="outline">#{index + 1}</Badge>
                                            <Badge variant="outline">{question.type}</Badge>
                                            <Badge variant="outline">{question.points} pt</Badge>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onclick={() => moveQuestion(question.id, -1)}
                                            disabled={index === 0 || reordering}
                                        >
                                            Up
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onclick={() => moveQuestion(question.id, 1)}
                                            disabled={index === questions.length - 1 || reordering}
                                        >
                                            Down
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-destructive hover:text-destructive"
                                            aria-label="Delete question"
                                            onclick={() => deleteQuestion(question)}
                                            disabled={deletingQuestions[question.id] || savingQuestions[question.id]}
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                    <div class="space-y-1.5 md:col-span-1">
                                        <Label for={`q-${question.id}-type-v2`}>Type</Label>
                                        <select
                                            id={`q-${question.id}-type-v2`}
                                            bind:value={question.type}
                                            onchange={() => {
                                                handleQuestionTypeChange(question);
                                                markDirty(question.id);
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
                                        <Label for={`q-${question.id}-prompt-v2`}>Prompt</Label>
                                        <Textarea
                                            id={`q-${question.id}-prompt-v2`}
                                            rows={2}
                                            bind:value={question.prompt}
                                            oninput={() => markDirty(question.id)}
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="w-28 space-y-1.5">
                                        <Label for={`q-${question.id}-points-v2`}>Points</Label>
                                        <Input
                                            id={`q-${question.id}-points-v2`}
                                            type="number"
                                            bind:value={question.points}
                                            oninput={() => markDirty(question.id)}
                                        />
                                    </div>
                                    <div class="flex-1"></div>
                                    {#if savedQuestions[question.id]}
                                        <Badge variant="secondary">Saved</Badge>
                                    {:else if dirtyQuestions[question.id]}
                                        <Badge variant="outline">Unsaved</Badge>
                                    {/if}
                                    <Button
                                        size="sm"
                                        onclick={() => saveQuestion(question, 'manual')}
                                        disabled={!dirtyQuestions[question.id] || savingQuestions[question.id]}
                                    >
                                        {savingQuestions[question.id] ? 'Saving…' : 'Save'}
                                    </Button>
                                </div>

                                {#if question.type !== 'essay'}
                                    <Separator />
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <Label>Options</Label>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="gap-1"
                                                onclick={() => addOption(question)}
                                                disabled={question.type === 'true_false'}
                                            >
                                                <Plus class="size-3.5" />
                                                Add option
                                            </Button>
                                        </div>
                                        {#each question.options as option, optionIndex (optionIndex)}
                                            <div class="flex items-start gap-2">
                                                {#if question.type === 'multiple'}
                                                    <input
                                                        type="checkbox"
                                                        bind:checked={option.is_correct}
                                                        onchange={() => markDirty(question.id)}
                                                        class="mt-2 rounded border-input"
                                                    />
                                                {:else}
                                                    <input
                                                        type="radio"
                                                        name={`correct-${question.id}`}
                                                        checked={option.is_correct}
                                                        onchange={() => setSingleCorrectOption(question, optionIndex)}
                                                        class="mt-2 rounded border-input"
                                                    />
                                                {/if}
                                                <Input
                                                    bind:value={option.text}
                                                    placeholder="Option text"
                                                    class="flex-1"
                                                    oninput={() => markDirty(question.id)}
                                                />
                                                {#if question.type !== 'true_false'}
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        aria-label="Remove option"
                                                        onclick={() => removeOption(question, optionIndex)}
                                                    >
                                                        <X class="size-4" />
                                                    </Button>
                                                {/if}
                                            </div>
                                        {/each}
                                        <p class="text-xs text-muted-foreground">
                                            Untuk type single atau true_false, pastikan hanya 1 jawaban yang benar.
                                        </p>
                                    </div>
                                {/if}
                            </div>
                        {/each}
                    </div>
                {/if}
            </CardContent>
        </Card>
    {/if}

    {#if activeTab === 'preview'}
        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <Card>
                <CardHeader>
                    <CardTitle>Preview & readiness</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge variant="outline">{examSummary.totalQuestions} questions</Badge>
                        <Badge variant="outline">{examSummary.totalPoints} total points</Badge>
                        <Badge variant={publishIssues.length === 0 ? 'secondary' : 'destructive'}>
                            {publishIssues.length === 0 ? 'Ready to publish' : 'Not ready'}
                        </Badge>
                    </div>

                    <div class="rounded-lg border p-4">
                        <p class="text-sm font-medium">Exam summary</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-md border p-3">
                                <p class="text-xs text-muted-foreground">Objective questions</p>
                                <p class="mt-1 text-lg font-semibold">{examSummary.objectiveCount}</p>
                            </div>
                            <div class="rounded-md border p-3">
                                <p class="text-xs text-muted-foreground">Essay questions</p>
                                <p class="mt-1 text-lg font-semibold">{examSummary.essayCount}</p>
                            </div>
                            <div class="rounded-md border p-3">
                                <p class="text-xs text-muted-foreground">Objective points</p>
                                <p class="mt-1 text-lg font-semibold">{examSummary.objectivePoints}</p>
                            </div>
                            <div class="rounded-md border p-3">
                                <p class="text-xs text-muted-foreground">Total points</p>
                                <p class="mt-1 text-lg font-semibold">{examSummary.totalPoints}</p>
                            </div>
                        </div>
                    </div>

                    {#if publishIssues.length > 0}
                        <div class="rounded-md border border-destructive/30 bg-destructive/5 p-4">
                            <p class="text-sm font-medium text-destructive">Blocker publish</p>
                            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                                {#each publishIssues as issue (issue.message + (issue.questionId ?? ''))}
                                    <li class="flex flex-wrap items-center justify-between gap-2">
                                        <span>{issue.message}</span>
                                        {#if issue.questionId}
                                            <Button variant="link" size="sm" class="h-auto p-0 text-xs" onclick={() => goToQuestion(issue.questionId)}>
                                                Fix in Builder
                                            </Button>
                                        {/if}
                                    </li>
                                {/each}
                            </ul>
                        </div>
                    {:else}
                        <div class="rounded-md border border-emerald-500/30 bg-emerald-500/5 p-4">
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Semua blocker sudah selesai.</p>
                            <p class="mt-1 text-sm text-muted-foreground">Exam siap untuk dipublish dari workspace baru ini.</p>
                        </div>
                    {/if}

                    {#if effectiveWarnings.length > 0}
                        <div class="rounded-md border p-4">
                            <p class="text-sm font-medium">Warnings</p>
                            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                                {#each effectiveWarnings as warning (warning.message + (warning.question_id ?? ''))}
                                    <li class="flex flex-wrap items-center justify-between gap-2">
                                        <span>{warning.message}</span>
                                        {#if warning.question_id}
                                            <Button variant="link" size="sm" class="h-auto p-0 text-xs" onclick={() => goToQuestion(warning.question_id)}>
                                                Lihat soal
                                            </Button>
                                        {/if}
                                    </li>
                                {/each}
                            </ul>
                        </div>
                    {/if}

                    <InputError message={form.errors.is_published} />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Actions</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="rounded-lg border p-4 text-sm">
                        <p class="font-medium">Current status</p>
                        <p class="mt-1 text-muted-foreground">
                            {form.is_published ? 'Exam sudah published.' : 'Exam masih draft.'}
                        </p>
                    </div>

                    <Button class="w-full" onclick={publishExam} disabled={form.processing || publishIssues.length > 0}>
                        Publish exam
                    </Button>
                    <Button class="w-full" variant="outline" onclick={saveDraft} disabled={form.processing}>
                        Save as draft
                    </Button>
                    <Button class="w-full" variant="ghost" onclick={() => setActiveTab('overview')}>
                        Back to overview
                    </Button>
                    <Button class="w-full" variant="ghost" onclick={() => setActiveTab('builder')}>
                        Back to builder
                    </Button>
                </CardContent>
            </Card>
        </div>
    {/if}

    {#if activeTab === 'attempts'}
        <Card>
            <CardHeader>
                <CardTitle>Recent attempts</CardTitle>
            </CardHeader>
            <CardContent>
                {#if attempts.length === 0}
                    <p class="text-sm text-muted-foreground">Belum ada attempt.</p>
                {:else}
                    <ul class="space-y-3">
                        {#each attempts as attempt (attempt.id)}
                            <li class="rounded-lg border p-3 text-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-medium">{attempt.user.name} · Attempt #{attempt.attempt_number}</div>
                                    <Badge variant={getAttemptStatusVariant(attempt)}>
                                        {getAttemptStatusLabel(attempt)}
                                    </Badge>
                                </div>
                                <div class="mt-1 text-xs text-muted-foreground">
                                    Started: {formatDateTime(attempt.started_at)}
                                    {#if attempt.submitted_at}
                                        <span class="px-1">·</span>
                                        Submitted: {formatDateTime(attempt.submitted_at)}
                                    {/if}
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                    {#if attempt.status === 'submitted'}
                                        <Badge variant="outline">
                                            Score: {attempt.score_points ?? 0}/{attempt.max_points}
                                        </Badge>
                                    {/if}
                                    <Link
                                        href={showAttemptRoute.url({ course: course.id, attempt: attempt.id })}
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
    {/if}
</div>
