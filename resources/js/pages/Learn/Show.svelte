<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import Check from 'lucide-svelte/icons/check';
    import CheckCircle2 from 'lucide-svelte/icons/check-circle-2';
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import Download from 'lucide-svelte/icons/download';
    import FileText from 'lucide-svelte/icons/file-text';
    import Link2 from 'lucide-svelte/icons/link-2';
    import PlayCircle from 'lucide-svelte/icons/play-circle';
    import AppHead from '@/components/AppHead.svelte';
    import PdfViewerModal from '@/components/PdfViewerModal.svelte';
    import { Avatar, AvatarFallback } from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Separator } from '@/components/ui/separator';
    import { getInitials } from '@/lib/initials';
    import { cn, getYouTubeEmbedUrl } from '@/lib/utils';
    import type {
        LessonRef,
        PlayerCourse,
        PlayerLesson,
        PlayerModule,
    } from '@/types';

    let {
        course,
        modules = [],
        current,
        previous = null,
        next = null,
        progress,
    }: {
        course: PlayerCourse;
        modules: PlayerModule[];
        current: PlayerLesson;
        previous: LessonRef | null;
        next: LessonRef | null;
        progress: { completed: number; total: number };
    } = $props();

    let activeTab = $state<'overview' | 'transcript' | 'resources'>('overview');
    let sidebarOpen = $state(true);
    let pdfModalOpen = $state(false);
    let pdfModalUrl = $state('');
    // svelte-ignore state_referenced_locally
    // eslint-disable-next-line
    let completed = $state(current.completed);

    $effect(() => {
        completed = current.completed;
    });

    const progressPercent = $derived(
        Math.round((progress.completed / progress.total) * 100),
    );

    function toggleComplete(): void {
        const url = `/lessons/${current.slug}/${completed ? 'incomplete' : 'complete'}`;

        router.post(url, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function openPdfModal(url: string) {
        pdfModalUrl = url;
        pdfModalOpen = true;
    }

    function iconForResource(type: string) {
        if (type === 'PDF') {
            return FileText;
        }

        if (type === 'Link') {
            return Link2;
        }

        return Download;
    }
</script>

<AppHead title={current.title} />

<div class="flex min-h-screen flex-col bg-background">
    <!-- Minimal top bar -->
    <header
        class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b bg-background/95 px-4 backdrop-blur"
    >
        <Link href={`/courses/${course.slug}`}>
            <Button variant="ghost" size="sm" class="gap-1.5">
                <ArrowLeft class="size-4" />
                Back to course
            </Button>
        </Link>
        <Separator orientation="vertical" class="h-6" />
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold">{course.title}</p>
            <p class="truncate text-xs text-muted-foreground">
                {current.module_title} · {current.title}
            </p>
        </div>
        <div class="hidden items-center gap-2 md:flex">
            <div class="flex w-40 flex-col gap-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-muted-foreground">Progress</span>
                    <span class="font-medium">{progressPercent}%</span>
                </div>
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full bg-primary transition-all"
                        style="width: {progressPercent}%"
                    ></div>
                </div>
            </div>
        </div>
        <Button
            variant="outline"
            size="sm"
            class="hidden lg:inline-flex"
            onclick={() => (sidebarOpen = !sidebarOpen)}
        >
            {sidebarOpen ? 'Hide' : 'Show'} sidebar
        </Button>
    </header>

    <!-- Main grid -->
    <div
        class={cn(
            'grid flex-1',
            sidebarOpen
                ? 'lg:grid-cols-[1fr_22rem]'
                : 'lg:grid-cols-[1fr_0]',
        )}
    >
        <!-- Video + content -->
        <main class="min-w-0 overflow-y-auto">
            <div class="bg-black">
                <div class="mx-auto aspect-video w-full max-w-5xl">
                    {#if getYouTubeEmbedUrl(current.video_url)}
                        <iframe
                            src={getYouTubeEmbedUrl(current.video_url)}
                            title="Lesson video"
                            class="h-full w-full"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    {:else}
                        <video
                            src={current.video_url}
                            controls
                            poster="https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=1200&q=80"
                            class="h-full w-full"
                        >
                            <track kind="captions" />
                        </video>
                    {/if}
                </div>
            </div>

            <div class="mx-auto w-full max-w-5xl px-4 py-6 md:px-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <Badge variant="secondary" class="mb-2 font-normal">
                            {current.module_title}
                        </Badge>
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                            {current.title}
                        </h1>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {current.duration} · Lesson {progress.completed + 1} of {progress.total}
                        </p>
                    </div>
                    <Button
                        variant={completed ? 'outline' : 'default'}
                        onclick={toggleComplete}
                        class="gap-1.5"
                    >
                        {#if completed}
                            <CheckCircle2 class="size-4 text-primary" />
                            Completed
                        {:else}
                            <Check class="size-4" />
                            Mark as complete
                        {/if}
                    </Button>
                </div>

                <!-- Tabs -->
                <div class="mt-6 flex items-center gap-1 border-b">
                    {#each ['overview', 'transcript', 'resources'] as const as tab (tab)}
                        <button
                            type="button"
                            class={cn(
                                'relative px-4 py-2.5 text-sm font-medium capitalize transition-colors',
                                activeTab === tab
                                    ? 'text-foreground'
                                    : 'text-muted-foreground hover:text-foreground',
                            )}
                            onclick={() => (activeTab = tab)}
                        >
                            {tab}
                            {#if activeTab === tab}
                                <span
                                    class="absolute inset-x-0 -bottom-px h-0.5 bg-primary"
                                ></span>
                            {/if}
                        </button>
                    {/each}
                </div>

                <!-- Tab content -->
                <div class="py-6">
                    {#if activeTab === 'overview'}
                        <div class="space-y-6">
                            <p class="text-sm leading-relaxed text-muted-foreground">
                                {current.description}
                            </p>
                            <div
                                class="flex items-center gap-3 rounded-lg border bg-accent/30 p-4"
                            >
                                <Avatar class="size-10">
                                    <AvatarFallback class="bg-primary/15 text-primary">
                                        {getInitials(course.instructor.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">
                                        {course.instructor.name}
                                    </p>
                                    <p class="truncate text-xs text-muted-foreground">
                                        {course.instructor.title}
                                    </p>
                                </div>
                            </div>
                        </div>
                    {:else if activeTab === 'transcript'}
                        <div
                            class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground"
                        >
                            {current.transcript}
                        </div>
                    {:else}
                        <ul class="space-y-2">
                            {#each current.resources as resource (resource.title)}
                                {@const Icon = iconForResource(resource.type)}
                                {@const isPdf = resource.type === 'PDF'}
                                <li>
                                    {#if isPdf}
                                        <button
                                            type="button"
                                            onclick={() => openPdfModal(resource.url)}
                                            class="flex w-full items-center gap-3 rounded-lg border p-3 text-left transition-colors hover:bg-accent/40"
                                        >
                                            <span
                                                class="flex size-9 items-center justify-center rounded-md bg-primary/15 text-primary"
                                            >
                                                <Icon class="size-4" />
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-medium">
                                                    {resource.title}
                                                </p>
                                                <p class="text-xs text-muted-foreground">
                                                    {resource.type}
                                                </p>
                                            </div>
                                        </button>
                                    {:else}
                                        <a
                                            href={resource.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent/40"
                                        >
                                            <span
                                                class="flex size-9 items-center justify-center rounded-md bg-primary/15 text-primary"
                                            >
                                                <Icon class="size-4" />
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-medium">
                                                    {resource.title}
                                                </p>
                                                <p class="text-xs text-muted-foreground">
                                                    {resource.type}
                                                </p>
                                            </div>
                                        </a>
                                    {/if}
                                </li>
                            {/each}
                        </ul>
                    {/if}
                </div>

                <!-- Prev / Next footer -->
                <div class="flex items-stretch justify-between gap-3 border-t pt-6">
                    {#if previous}
                        <Link
                            href={`/learn/${course.slug}/${previous.slug}`}
                            class="group flex min-w-0 flex-1 items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent/40"
                        >
                            <ChevronLeft
                                class="size-5 shrink-0 text-muted-foreground"
                            />
                            <div class="min-w-0 text-left">
                                <p class="text-xs text-muted-foreground">
                                    Previous
                                </p>
                                <p class="truncate text-sm font-medium">
                                    {previous.title}
                                </p>
                            </div>
                        </Link>
                    {:else}
                        <div class="flex-1"></div>
                    {/if}
                    {#if next}
                        <Link
                            href={`/learn/${course.slug}/${next.slug}`}
                            class="group flex min-w-0 flex-1 items-center justify-end gap-3 rounded-lg border bg-primary p-3 text-primary-foreground transition-opacity hover:opacity-90"
                        >
                            <div class="min-w-0 text-right">
                                <p class="text-xs opacity-80">Next</p>
                                <p class="truncate text-sm font-medium">
                                    {next.title}
                                </p>
                            </div>
                            <ChevronRight class="size-5 shrink-0" />
                        </Link>
                    {:else}
                        <div class="flex-1 rounded-lg border bg-accent/30 p-3 text-center text-sm text-muted-foreground">
                            You've reached the end of the course
                        </div>
                    {/if}
                </div>
            </div>
        </main>

        <!-- Lesson sidebar -->
        {#if sidebarOpen}
            <aside
                class="hidden overflow-y-auto border-l lg:block"
            >
                <div class="sticky top-0 border-b bg-background px-4 py-3">
                    <p class="text-sm font-semibold">Course content</p>
                    <p class="text-xs text-muted-foreground">
                        {progress.completed}/{progress.total} lessons complete
                    </p>
                </div>
                <div>
                    {#each modules as module, mIndex (module.title)}
                        <div class="border-b last:border-b-0">
                            <div class="bg-muted/40 px-4 py-2.5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    Module {mIndex + 1}
                                </p>
                                <p class="text-sm font-medium">
                                    {module.title}
                                </p>
                            </div>
                            <ul>
                                {#each module.lessons as lesson (lesson.slug)}
                                    {@const isActive = lesson.slug === current.slug}
                                    <li>
                                        <Link
                                            href={`/learn/${course.slug}/${lesson.slug}`}
                                            class={cn(
                                                'flex items-center gap-3 px-4 py-2.5 text-sm transition-colors',
                                                isActive
                                                    ? 'bg-primary/10 border-l-2 border-primary text-foreground'
                                                    : 'border-l-2 border-transparent text-muted-foreground hover:bg-accent/40 hover:text-foreground',
                                            )}
                                        >
                                            {#if lesson.completed}
                                                <CheckCircle2
                                                    class="size-4 shrink-0 text-primary"
                                                />
                                            {:else}
                                                <PlayCircle
                                                    class={cn(
                                                        'size-4 shrink-0',
                                                        isActive
                                                            ? 'text-primary'
                                                            : 'text-muted-foreground',
                                                    )}
                                                />
                                            {/if}
                                            <span class="min-w-0 flex-1 truncate">
                                                {lesson.title}
                                            </span>
                                            <span class="shrink-0 text-xs text-muted-foreground">
                                                {lesson.duration}
                                            </span>
                                        </Link>
                                    </li>
                                {/each}
                            </ul>
                        </div>
                    {/each}
                </div>
            </aside>
        {/if}
    </div>
</div>

<PdfViewerModal bind:open={pdfModalOpen} url={pdfModalUrl} />
