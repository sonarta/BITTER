<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Teaching', href: '/instructor' },
            { title: 'My Courses', href: '/instructor/courses' },
            { title: 'Curriculum', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import { router, useForm } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import Clock from 'lucide-svelte/icons/clock';
    import Eye from 'lucide-svelte/icons/eye';
    import GripVertical from 'lucide-svelte/icons/grip-vertical';
    import Pencil from 'lucide-svelte/icons/pencil';
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import X from 'lucide-svelte/icons/x';
    import AppHead from '@/components/AppHead.svelte';
    import InstructorNav from '@/components/InstructorNav.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';

    type Lesson = {
        id: string;
        title: string;
        slug: string;
        duration_seconds: number;
        is_preview: boolean;
        video_url: string | null;
        content: string | null;
        sort_order: number;
    };

    type Module = {
        id: string;
        title: string;
        sort_order: number;
        lessons: Lesson[];
    };

    type CourseData = {
        id: string;
        title: string;
        slug: string;
        status: string;
        modules: Module[];
    };

    let { course }: { course: CourseData } = $props();

    // --- Module add ---
    let showAddModule = $state(false);
    const moduleForm = useForm({ title: '' });

    function addModule(e: SubmitEvent) {
        e.preventDefault();
        moduleForm.post(`/instructor/courses/${course.id}/modules`, {
            preserveScroll: true,
            onSuccess: () => {
                moduleForm.reset();
                showAddModule = false;
            },
        });
    }

    // --- Module edit ---
    let editingModuleId = $state<string | null>(null);
    const editModuleForm = useForm({ title: '' });

    function startEditModule(mod: Module) {
        editingModuleId = mod.id;
        editModuleForm.title = mod.title;
    }

    function saveEditModule(e: SubmitEvent) {
        e.preventDefault();
        if (!editingModuleId) return;
        editModuleForm.put(
            `/instructor/courses/${course.id}/modules/${editingModuleId}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    editingModuleId = null;
                },
            },
        );
    }

    function deleteModule(moduleId: string) {
        if (!confirm('Delete this module and all its lessons?')) return;
        router.delete(
            `/instructor/courses/${course.id}/modules/${moduleId}`,
            { preserveScroll: true },
        );
    }

    // --- Lesson add ---
    let addingLessonModuleId = $state<string | null>(null);
    const lessonForm = useForm({
        title: '',
        content: '',
        video_url: '',
        duration_seconds: 600,
        is_preview: false,
    });

    function startAddLesson(moduleId: string) {
        addingLessonModuleId = moduleId;
        lessonForm.reset();
    }

    function addLesson(e: SubmitEvent) {
        e.preventDefault();
        if (!addingLessonModuleId) return;
        lessonForm.post(
            `/instructor/courses/${course.id}/modules/${addingLessonModuleId}/lessons`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    addingLessonModuleId = null;
                    lessonForm.reset();
                },
            },
        );
    }

    // --- Lesson edit ---
    let editingLessonId = $state<string | null>(null);
    let editingLessonModuleId = $state<string | null>(null);
    const editLessonForm = useForm({
        title: '',
        content: '',
        video_url: '',
        duration_seconds: 0,
        is_preview: false,
    });

    function startEditLesson(mod: Module, lesson: Lesson) {
        editingLessonId = lesson.id;
        editingLessonModuleId = mod.id;
        editLessonForm.title = lesson.title;
        editLessonForm.content = lesson.content ?? '';
        editLessonForm.video_url = lesson.video_url ?? '';
        editLessonForm.duration_seconds = lesson.duration_seconds;
        editLessonForm.is_preview = lesson.is_preview;
    }

    function saveEditLesson(e: SubmitEvent) {
        e.preventDefault();
        if (!editingLessonId || !editingLessonModuleId) return;
        editLessonForm.put(
            `/instructor/courses/${course.id}/modules/${editingLessonModuleId}/lessons/${editingLessonId}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    editingLessonId = null;
                    editingLessonModuleId = null;
                },
            },
        );
    }

    function deleteLesson(moduleId: string, lessonId: string) {
        if (!confirm('Delete this lesson?')) return;
        router.delete(
            `/instructor/courses/${course.id}/modules/${moduleId}/lessons/${lessonId}`,
            { preserveScroll: true },
        );
    }

    function formatDuration(seconds: number): string {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    const totalLessons = $derived(
        course.modules.reduce((sum, m) => sum + m.lessons.length, 0),
    );
    const totalDuration = $derived(
        course.modules.reduce(
            (sum, m) =>
                sum +
                m.lessons.reduce((ls, l) => ls + l.duration_seconds, 0),
            0,
        ),
    );
</script>

<AppHead title="Curriculum — {course.title}" />

<InstructorNav active="courses" />

<div class="mx-auto w-full max-w-4xl space-y-6 px-4 py-8">
    <!-- Header -->
    <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Curriculum</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {course.title} —
                <span class="inline-flex items-center gap-2">
                    <BookOpen class="size-3.5" />
                    {totalLessons} lessons
                    <Clock class="ml-1 size-3.5" />
                    {formatDuration(totalDuration)}
                </span>
            </p>
        </div>
        <Button
            variant="outline"
            onclick={() => (showAddModule = !showAddModule)}
        >
            <Plus class="mr-2 size-4" />
            Add Module
        </Button>
    </section>

    <!-- Add module form -->
    {#if showAddModule}
        <Card>
            <CardContent class="pt-6">
                <form onsubmit={addModule} class="flex items-end gap-3">
                    <div class="flex-1 space-y-1.5">
                        <Label for="new-module">Module Title</Label>
                        <Input
                            id="new-module"
                            bind:value={moduleForm.title}
                            placeholder="e.g. Pengenalan Kewirausahaan"
                        />
                        <InputError message={moduleForm.errors.title} />
                    </div>
                    <Button type="submit" disabled={moduleForm.processing}>
                        Add
                    </Button>
                    <Button
                        variant="ghost"
                        type="button"
                        onclick={() => (showAddModule = false)}
                    >
                        <X class="size-4" />
                    </Button>
                </form>
            </CardContent>
        </Card>
    {/if}

    <!-- Modules list -->
    {#if course.modules.length === 0}
        <Card>
            <CardContent class="flex flex-col items-center gap-3 py-16">
                <BookOpen class="size-10 text-muted-foreground" />
                <p class="text-sm font-medium">No modules yet</p>
                <p class="text-xs text-muted-foreground">
                    Start by adding a module above.
                </p>
            </CardContent>
        </Card>
    {:else}
        {#each course.modules as mod, moduleIndex (mod.id)}
            <Card>
                <CardHeader class="flex flex-row items-center gap-3">
                    <GripVertical
                        class="size-5 shrink-0 cursor-grab text-muted-foreground"
                    />
                    <div class="min-w-0 flex-1">
                        {#if editingModuleId === mod.id}
                            <form
                                onsubmit={saveEditModule}
                                class="flex items-center gap-2"
                            >
                                <Input
                                    bind:value={editModuleForm.title}
                                    class="h-8"
                                />
                                <Button
                                    size="sm"
                                    type="submit"
                                    disabled={editModuleForm.processing}
                                    >Save</Button
                                >
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    type="button"
                                    onclick={() => (editingModuleId = null)}
                                >
                                    <X class="size-3.5" />
                                </Button>
                            </form>
                        {:else}
                            <CardTitle class="text-base">
                                <span class="text-muted-foreground"
                                    >Module {moduleIndex + 1}:</span
                                >
                                {mod.title}
                            </CardTitle>
                        {/if}
                    </div>
                    {#if editingModuleId !== mod.id}
                        <div class="flex items-center gap-1">
                            <Badge variant="secondary" class="font-normal">
                                {mod.lessons.length} lessons
                            </Badge>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="size-8"
                                onclick={() => startEditModule(mod)}
                            >
                                <Pencil class="size-3.5" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="size-8 text-destructive hover:text-destructive"
                                onclick={() => deleteModule(mod.id)}
                            >
                                <Trash2 class="size-3.5" />
                            </Button>
                        </div>
                    {/if}
                </CardHeader>
                <CardContent class="space-y-2 pt-0">
                    <!-- Lessons -->
                    {#each mod.lessons as lesson (lesson.id)}
                        {#if editingLessonId === lesson.id}
                            <!-- Edit lesson form -->
                            <form
                                onsubmit={saveEditLesson}
                                class="space-y-3 rounded-lg border bg-muted/30 p-4"
                            >
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="space-y-1.5">
                                        <Label>Title</Label>
                                        <Input
                                            bind:value={editLessonForm.title}
                                        />
                                        <InputError
                                            message={editLessonForm.errors
                                                .title}
                                        />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label>Duration (seconds)</Label>
                                        <Input
                                            type="number"
                                            bind:value={editLessonForm.duration_seconds}
                                        />
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <Label>Video URL</Label>
                                    <Input
                                        bind:value={editLessonForm.video_url}
                                        placeholder="https://..."
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label>Content</Label>
                                    <Textarea
                                        bind:value={editLessonForm.content}
                                        rows={3}
                                    />
                                </div>
                                <div class="flex items-center gap-4">
                                    <label
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            bind:checked={editLessonForm.is_preview}
                                            class="rounded border-input"
                                        />
                                        Free preview
                                    </label>
                                    <div class="flex-1"></div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        type="button"
                                        onclick={() => {
                                            editingLessonId = null;
                                            editingLessonModuleId = null;
                                        }}>Cancel</Button
                                    >
                                    <Button
                                        size="sm"
                                        type="submit"
                                        disabled={editLessonForm.processing}
                                        >Save</Button
                                    >
                                </div>
                            </form>
                        {:else}
                            <!-- Lesson row -->
                            <div
                                class="group flex items-center gap-3 rounded-lg border px-4 py-3 transition-colors hover:bg-accent/30"
                            >
                                <GripVertical
                                    class="size-4 shrink-0 cursor-grab text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100"
                                />
                                <BookOpen
                                    class="size-4 shrink-0 text-primary"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">
                                        {lesson.title}
                                    </p>
                                </div>
                                {#if lesson.is_preview}
                                    <Badge
                                        variant="outline"
                                        class="border-emerald-500/40 text-xs font-normal text-emerald-700 dark:text-emerald-400"
                                    >
                                        <Eye class="mr-1 size-3" />
                                        Preview
                                    </Badge>
                                {/if}
                                <span
                                    class="text-xs text-muted-foreground tabular-nums"
                                >
                                    {formatDuration(lesson.duration_seconds)}
                                </span>
                                <div
                                    class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="size-7"
                                        onclick={() =>
                                            startEditLesson(mod, lesson)}
                                    >
                                        <Pencil class="size-3" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="size-7 text-destructive hover:text-destructive"
                                        onclick={() =>
                                            deleteLesson(mod.id, lesson.id)}
                                    >
                                        <Trash2 class="size-3" />
                                    </Button>
                                </div>
                            </div>
                        {/if}
                    {/each}

                    <!-- Add lesson form -->
                    {#if addingLessonModuleId === mod.id}
                        <form
                            onsubmit={addLesson}
                            class="space-y-3 rounded-lg border border-dashed bg-muted/20 p-4"
                        >
                            <div
                                class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                            >
                                <div class="space-y-1.5">
                                    <Label>Title</Label>
                                    <Input
                                        bind:value={lessonForm.title}
                                        placeholder="e.g. Pertemuan 1: Orientasi"
                                    />
                                    <InputError
                                        message={lessonForm.errors.title}
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label>Duration (seconds)</Label>
                                    <Input
                                        type="number"
                                        bind:value={lessonForm.duration_seconds}
                                    />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <Label>Video URL</Label>
                                <Input
                                    bind:value={lessonForm.video_url}
                                    placeholder="https://..."
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label>Content</Label>
                                <Textarea
                                    bind:value={lessonForm.content}
                                    rows={3}
                                    placeholder="Lesson content..."
                                />
                            </div>
                            <div class="flex items-center gap-4">
                                <label
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <input
                                        type="checkbox"
                                        bind:checked={lessonForm.is_preview}
                                        class="rounded border-input"
                                    />
                                    Free preview
                                </label>
                                <div class="flex-1"></div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    type="button"
                                    onclick={() =>
                                        (addingLessonModuleId = null)}
                                    >Cancel</Button
                                >
                                <Button
                                    size="sm"
                                    type="submit"
                                    disabled={lessonForm.processing}
                                    >Add Lesson</Button
                                >
                            </div>
                        </form>
                    {:else}
                        <button
                            class="flex w-full items-center justify-center gap-2 rounded-lg border border-dashed py-2.5 text-xs text-muted-foreground transition-colors hover:border-primary hover:text-primary"
                            onclick={() => startAddLesson(mod.id)}
                        >
                            <Plus class="size-3.5" />
                            Add lesson
                        </button>
                    {/if}
                </CardContent>
            </Card>
        {/each}
    {/if}
</div>
