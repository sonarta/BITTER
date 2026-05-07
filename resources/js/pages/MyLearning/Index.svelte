<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'My Learning', href: '/my-learning' },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import Clock from 'lucide-svelte/icons/clock';
    import PlayCircle from 'lucide-svelte/icons/play-circle';
    import Trophy from 'lucide-svelte/icons/trophy';
    import AppHead from '@/components/AppHead.svelte';
    import CourseCover from '@/components/CourseCover.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { cn } from '@/lib/utils';
    import type { EnrolledCourse } from '@/types';

    let {
        enrolled = [],
    }: {
        enrolled: EnrolledCourse[];
    } = $props();

    let activeTab = $state<'in_progress' | 'completed'>('in_progress');

    const inProgress = $derived(enrolled.filter((c) => c.progress < 100));
    const completed = $derived(enrolled.filter((c) => c.progress >= 100));
    const visible = $derived(
        activeTab === 'in_progress' ? inProgress : completed,
    );
</script>

<AppHead title="My Learning" />

<div class="mx-auto w-full max-w-7xl px-4 py-8">
    <header class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight">My Learning</h1>
        <p class="mt-1 text-sm text-muted-foreground">
            Pick up where you left off.
        </p>
    </header>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <Card>
            <CardContent class="flex items-center gap-4 pt-6">
                <div class="flex size-11 items-center justify-center rounded-full bg-primary/15 text-primary">
                    <BookOpen class="size-5" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{enrolled.length}</p>
                    <p class="text-xs text-muted-foreground">Enrolled</p>
                </div>
            </CardContent>
        </Card>
        <Card>
            <CardContent class="flex items-center gap-4 pt-6">
                <div class="flex size-11 items-center justify-center rounded-full bg-primary/15 text-primary">
                    <Clock class="size-5" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{inProgress.length}</p>
                    <p class="text-xs text-muted-foreground">In progress</p>
                </div>
            </CardContent>
        </Card>
        <Card>
            <CardContent class="flex items-center gap-4 pt-6">
                <div class="flex size-11 items-center justify-center rounded-full bg-primary/15 text-primary">
                    <Trophy class="size-5" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{completed.length}</p>
                    <p class="text-xs text-muted-foreground">Completed</p>
                </div>
            </CardContent>
        </Card>
    </div>

    <div class="mb-5 inline-flex items-center rounded-lg bg-muted p-1">
        <Button
            variant={activeTab === 'in_progress' ? 'default' : 'ghost'}
            size="sm"
            class="rounded-md"
            onclick={() => (activeTab = 'in_progress')}
        >
            In progress ({inProgress.length})
        </Button>
        <Button
            variant={activeTab === 'completed' ? 'default' : 'ghost'}
            size="sm"
            class="rounded-md"
            onclick={() => (activeTab = 'completed')}
        >
            Completed ({completed.length})
        </Button>
    </div>

    {#if visible.length === 0}
        <Card class="border-dashed">
            <CardContent class="flex flex-col items-center gap-3 py-16 text-center">
                <BookOpen class="size-8 text-muted-foreground" />
                <p class="text-sm font-medium">
                    {activeTab === 'in_progress'
                        ? 'No courses in progress'
                        : 'No courses completed yet'}
                </p>
                <p class="max-w-sm text-xs text-muted-foreground">
                    Browse the catalog and enroll in a course to start learning.
                </p>
                <Link href="/courses">
                    <Button size="sm">Browse courses</Button>
                </Link>
            </CardContent>
        </Card>
    {:else}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {#each visible as course (course.slug)}
                <Card class="overflow-hidden">
                    <div class="flex gap-4">
                        <CourseCover
                            src={course.cover}
                            source={course.cover_source}
                            title={course.title}
                            loading="lazy"
                            showBadge={false}
                            class="aspect-square w-32 shrink-0 overflow-hidden sm:w-40"
                        />
                        <div class="flex min-w-0 flex-1 flex-col justify-between py-4 pr-4">
                            <div>
                                <Badge variant="secondary" class="mb-2 font-normal">
                                    {course.category}
                                </Badge>
                                {#if course.cover_source !== 'manual'}
                                    <Badge variant="outline" class="mb-2 ml-2 font-normal">
                                        Default cover
                                    </Badge>
                                {/if}
                                <h3 class="truncate font-semibold">
                                    {course.title}
                                </h3>
                                <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                    {course.progress === 100
                                        ? 'Course completed'
                                        : `Up next: ${course.last_lesson}`}
                                </p>
                            </div>

                            <div class="mt-3 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-muted-foreground">
                                        {course.progress}% complete
                                    </span>
                                </div>
                                <div
                                    class="h-1.5 w-full overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class={cn(
                                            'h-full rounded-full bg-primary transition-all',
                                        )}
                                        style="width: {course.progress}%"
                                    ></div>
                                </div>
                                <Link
                                    href={`/learn/${course.slug}`}
                                    class="inline-block"
                                >
                                    <Button size="sm" class="mt-1">
                                        <PlayCircle class="mr-1.5 size-4" />
                                        {course.progress === 100
                                            ? 'Review'
                                            : 'Continue'}
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </Card>
            {/each}
        </div>
    {/if}
</div>
