<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Courses', href: '/courses' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import CheckCircle2 from 'lucide-svelte/icons/check-circle-2';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import Clock from 'lucide-svelte/icons/clock';
    import PlayCircle from 'lucide-svelte/icons/play-circle';
    import Star from 'lucide-svelte/icons/star';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import CourseCover from '@/components/CourseCover.svelte';
    import { Avatar, AvatarFallback } from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import { getInitials } from '@/lib/initials';
    import type { Course, CourseModule } from '@/types';

    let {
        course,
        modules = [],
        related = [],
        is_enrolled = false,
        exam = null,
    }: {
        course: Course;
        modules: CourseModule[];
        related: Course[];
        is_enrolled?: boolean;
        exam?: {
            title: string;
            is_published: boolean;
            duration_minutes: number;
            max_attempts: number;
            pass_score: number;
        } | null;
    } = $props();

    const totalLessons = $derived(
        modules.reduce((acc, module) => acc + module.lessons.length, 0),
    );

    let expanded = $state<Record<number, boolean>>({ 0: true });

    function toggle(index: number): void {
        expanded[index] = !expanded[index];
    }
</script>

<AppHead title={course.title} />

<div class="mx-auto w-full max-w-7xl px-4 py-8">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <Badge variant="secondary">{course.category}</Badge>
                    <Badge variant="outline">{course.level}</Badge>
                </div>
                <h1 class="mt-3 text-3xl font-bold tracking-tight md:text-4xl">
                    {course.title}
                </h1>
                <p class="mt-2 text-muted-foreground">{course.tagline}</p>

                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
                    <span class="inline-flex items-center gap-1.5">
                        <Star class="size-4 fill-primary text-primary" />
                        <span class="font-medium text-foreground">{course.rating.toFixed(1)}</span>
                        rating
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <Users class="size-4" />
                        {course.students.toLocaleString()} students
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <Clock class="size-4" />
                        {course.duration_hours}h total
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <BookOpen class="size-4" />
                        {course.lessons_count} lessons
                    </span>
                </div>
            </div>

            <div class="aspect-video overflow-hidden rounded-xl border bg-muted">
                <CourseCover
                    src={course.cover}
                    source={course.cover_source}
                    title={course.title}
                    loading="lazy"
                    class="h-full w-full"
                />
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>About this course</CardTitle>
                </CardHeader>
                <CardContent class="text-sm leading-relaxed text-muted-foreground">
                    {course.description}
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Curriculum</CardTitle>
                    <span class="text-xs text-muted-foreground">
                        {modules.length} modules · {totalLessons} lessons
                    </span>
                </CardHeader>
                <CardContent class="p-0">
                    <ul class="divide-y">
                        {#each modules as module, index (module.title)}
                            <li>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-accent/50"
                                    onclick={() => toggle(index)}
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-7 items-center justify-center rounded-full bg-primary/15 text-xs font-semibold text-primary">
                                            {index + 1}
                                        </span>
                                        <span class="font-medium">{module.title}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-muted-foreground">
                                        <span>{module.lessons.length} lessons</span>
                                        <ChevronRight
                                            class="size-4 transition-transform {expanded[index] ? 'rotate-90' : ''}"
                                        />
                                    </div>
                                </button>
                                {#if expanded[index]}
                                    <ul class="space-y-1 px-6 pb-4">
                                        {#each module.lessons as lesson (lesson.title)}
                                            <li class="flex items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-accent/40">
                                                <div class="flex items-center gap-3">
                                                    <PlayCircle class="size-4 text-muted-foreground" />
                                                    <span>{lesson.title}</span>
                                                    {#if lesson.preview}
                                                        <Badge variant="outline" class="h-5 text-[10px]">
                                                            Preview
                                                        </Badge>
                                                    {/if}
                                                </div>
                                                <span class="text-xs text-muted-foreground">
                                                    {lesson.duration}
                                                </span>
                                            </li>
                                        {/each}
                                    </ul>
                                {/if}
                            </li>
                        {/each}
                    </ul>
                </CardContent>
            </Card>

            {#if related.length > 0}
                <div>
                    <h2 class="mb-3 text-lg font-semibold">Related courses</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {#each related as item (item.slug)}
                            <Link
                                href={`/courses/${item.slug}`}
                                class="group rounded-lg outline-none focus-visible:ring-2 focus-visible:ring-primary"
                            >
                                <Card class="h-full transition-shadow hover:shadow-md">
                                    <CardContent class="flex gap-3 p-3">
                                        <CourseCover
                                            src={item.cover}
                                            source={item.cover_source}
                                            title={item.title}
                                            loading="lazy"
                                            showBadge={false}
                                            class="size-20 shrink-0 overflow-hidden rounded-md"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium group-hover:text-primary">
                                                {item.title}
                                            </p>
                                            <p class="mt-0.5 line-clamp-2 text-xs text-muted-foreground">
                                                {item.tagline}
                                            </p>
                                            <div class="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                                <Star class="size-3 fill-primary text-primary" />
                                                {item.rating.toFixed(1)}
                                                <span class="px-1">·</span>
                                                <span>
                                                    {item.cover_source === 'manual'
                                                        ? 'Manual cover'
                                                        : 'Default cover'}
                                                </span>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        {/each}
                    </div>
                </div>
            {/if}
        </div>

        <aside class="lg:col-span-1">
            <div class="sticky top-6 space-y-4">
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold">
                                {course.price === 0 ? 'Free' : `$${course.price}`}
                            </span>
                            {#if course.price > 0}
                                <span class="text-xs text-muted-foreground line-through">
                                    ${course.price + 20}
                                </span>
                            {/if}
                        </div>

                        {#if is_enrolled}
                            <Link href={`/learn/${course.slug}`} class="block">
                                <Button size="lg" class="w-full">
                                    Continue Learning
                                </Button>
                            </Link>
                        {:else}
                            <Button
                                size="lg"
                                class="w-full"
                                onclick={() => router.post(`/courses/${course.slug}/enroll`)}
                            >
                                Enroll & Start Learning
                            </Button>
                        {/if}

                        {#if exam}
                            <Link
                                href={`/courses/${course.slug}/exam`}
                                class="block"
                                aria-disabled={!is_enrolled || !exam.is_published}
                            >
                                <Button
                                    variant="secondary"
                                    class="w-full"
                                    disabled={!is_enrolled || !exam.is_published}
                                >
                                    Take Final Exam
                                </Button>
                            </Link>
                            {#if !exam.is_published}
                                <p class="-mt-2 text-xs text-muted-foreground">
                                    Exam belum dipublish oleh instructor.
                                </p>
                            {:else if !is_enrolled}
                                <p class="-mt-2 text-xs text-muted-foreground">
                                    Enroll dulu untuk mengikuti exam.
                                </p>
                            {/if}
                        {/if}

                        <Separator />

                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center gap-2">
                                <CheckCircle2 class="size-4 text-primary" />
                                {course.duration_hours}h of on-demand video
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle2 class="size-4 text-primary" />
                                {course.lessons_count} lessons
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle2 class="size-4 text-primary" />
                                Certificate of completion
                            </li>
                            <li class="flex items-center gap-2">
                                <CheckCircle2 class="size-4 text-primary" />
                                Lifetime access
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm">Instructor</CardTitle>
                    </CardHeader>
                    <CardContent class="flex items-center gap-3">
                        <Avatar class="size-10">
                            <AvatarFallback class="bg-primary/15 text-primary">
                                {getInitials(course.instructor.name)}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <p class="text-sm font-medium">
                                {course.instructor.name}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {course.instructor.title}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </aside>
    </div>
</div>
