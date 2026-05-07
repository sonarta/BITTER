<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Award from 'lucide-svelte/icons/award';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import CheckCircle2 from 'lucide-svelte/icons/check-circle-2';
    import Clock from 'lucide-svelte/icons/clock';
    import Flame from 'lucide-svelte/icons/flame';
    import PlayCircle from 'lucide-svelte/icons/play-circle';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Star from 'lucide-svelte/icons/star';
    import Trophy from 'lucide-svelte/icons/trophy';
    import AppHead from '@/components/AppHead.svelte';
    import CourseCover from '@/components/CourseCover.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { cn } from '@/lib/utils';

    type StatIcon = 'BookOpen' | 'Clock' | 'Trophy' | 'Award';
    type ActivityIcon = 'CheckCircle2' | 'Award' | 'PlayCircle';

    type Stat = { label: string; value: number; icon: StatIcon };
    type ContinueItem = {
        slug: string;
        title: string;
        module_title: string;
        lesson_slug: string;
        lesson_title: string;
        progress: number;
        cover: string;
        cover_source: 'manual' | 'placeholder';
        remaining: string;
    };
    type Recommended = {
        slug: string;
        title: string;
        category: string;
        level: string;
        duration_hours: number;
        rating: number;
        cover: string;
        cover_source: 'manual' | 'placeholder';
    };
    type Activity = {
        label: string;
        detail: string;
        when: string;
        icon: ActivityIcon;
    };
    type Streak = {
        days: number;
        goal_minutes: number;
        minutes_today: number;
    };

    let {
        stats = [],
        continue_learning = [],
        recommended = [],
        activity = [],
        streak,
    }: {
        stats: Stat[];
        continue_learning: ContinueItem[];
        recommended: Recommended[];
        activity: Activity[];
        streak: Streak;
    } = $props();

    const auth = $derived(page.props.auth);
    const firstName = $derived(
        (auth.user?.name ?? 'there').split(' ')[0],
    );

    const statIcons = {
        BookOpen,
        Clock,
        Trophy,
        Award,
    } as const;

    const activityIcons = {
        CheckCircle2,
        Award,
        PlayCircle,
    } as const;

    const goalPercent = $derived(
        Math.min(
            100,
            Math.round((streak.minutes_today / streak.goal_minutes) * 100),
        ),
    );
</script>

<AppHead title="Dashboard" />

<div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-8">
    <!-- Greeting -->
    <section class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm text-muted-foreground">
                {new Date().toLocaleDateString('en-US', {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                })}
            </p>
            <h1 class="text-3xl font-bold tracking-tight">
                Welcome back, {firstName} 👋
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Pick up where you left off or discover something new.
            </p>
        </div>
        <div
            class="flex items-center gap-3 rounded-xl border bg-primary/10 px-4 py-3"
        >
            <Flame class="size-5 text-primary" />
            <div>
                <p class="text-sm font-semibold">
                    {streak.days}-day streak
                </p>
                <p class="text-xs text-muted-foreground">
                    {streak.minutes_today}/{streak.goal_minutes} min today
                </p>
            </div>
            <div class="h-8 w-24">
                <div
                    class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-background"
                >
                    <div
                        class="h-full rounded-full bg-primary transition-all"
                        style="width: {goalPercent}%"
                    ></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="grid grid-cols-2 gap-4 md:grid-cols-4">
        {#each stats as stat (stat.label)}
            {@const Icon = statIcons[stat.icon]}
            <Card>
                <CardContent class="flex items-center gap-4 pt-6">
                    <div
                        class="flex size-11 items-center justify-center rounded-full bg-primary/15 text-primary"
                    >
                        <Icon class="size-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{stat.value}</p>
                        <p class="text-xs text-muted-foreground">
                            {stat.label}
                        </p>
                    </div>
                </CardContent>
            </Card>
        {/each}
    </section>

    <!-- Continue learning -->
    <section>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Continue learning</h2>
            <Link
                href="/my-learning"
                class="text-sm font-medium text-primary hover:underline"
            >
                View all
            </Link>
        </div>

        {#if continue_learning.length === 0}
            <Card class="border-dashed">
                <CardContent class="flex flex-col items-center gap-3 py-14 text-center">
                    <BookOpen class="size-8 text-muted-foreground" />
                    <p class="text-sm font-medium">Nothing in progress yet</p>
                    <Link href="/courses">
                        <Button size="sm">Browse courses</Button>
                    </Link>
                </CardContent>
            </Card>
        {:else}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                {#each continue_learning as item (item.slug)}
                    <Card class="overflow-hidden">
                        <div class="flex gap-4">
                            <div class="relative w-40 shrink-0">
                                <CourseCover
                                    src={item.cover}
                                    source={item.cover_source}
                                    title={item.title}
                                    loading="lazy"
                                    showBadge={false}
                                    class="h-full w-full"
                                />
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 transition-opacity hover:opacity-100"
                                >
                                    <PlayCircle class="size-10 text-white" />
                                </div>
                            </div>
                            <div class="flex min-w-0 flex-1 flex-col justify-between py-4 pr-4">
                                <div class="min-w-0">
                                    <p class="truncate text-xs text-muted-foreground">
                                        {item.module_title}
                                        <span class="px-1">·</span>
                                        <span>
                                            {item.cover_source === 'manual'
                                                ? 'Manual cover'
                                                : 'Default cover'}
                                        </span>
                                    </p>
                                    <h3 class="truncate font-semibold">
                                        {item.title}
                                    </h3>
                                    <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                        Up next: {item.lesson_title}
                                    </p>
                                </div>
                                <div class="mt-3 space-y-2">
                                    <div
                                        class="flex items-center justify-between text-xs text-muted-foreground"
                                    >
                                        <span>{item.progress}% · {item.remaining}</span>
                                    </div>
                                    <div
                                        class="h-1.5 w-full overflow-hidden rounded-full bg-muted"
                                    >
                                        <div
                                            class="h-full rounded-full bg-primary transition-all"
                                            style="width: {item.progress}%"
                                        ></div>
                                    </div>
                                    <Link
                                        href={`/learn/${item.slug}/${item.lesson_slug}`}
                                        class="inline-block"
                                    >
                                        <Button size="sm" class="mt-1">
                                            <PlayCircle class="mr-1.5 size-4" />
                                            Resume
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </Card>
                {/each}
            </div>
        {/if}
    </section>

    <!-- Bottom grid: recommended + activity -->
    <section class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-lg font-semibold">
                    <Sparkles class="size-4 text-primary" />
                    Recommended for you
                </h2>
                <Link
                    href="/courses"
                    class="text-sm font-medium text-primary hover:underline"
                >
                    Browse catalog
                </Link>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {#each recommended as course (course.slug)}
                    <Link
                        href={`/courses/${course.slug}`}
                        class="group rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                        <Card class="h-full overflow-hidden pt-0 pb-4 transition-shadow hover:shadow-md">
                            <div class="aspect-video w-full overflow-hidden bg-muted">
                                <CourseCover
                                    src={course.cover}
                                    source={course.cover_source}
                                    title={course.title}
                                    loading="lazy"
                                    class="h-full w-full"
                                    imgClass="transition-transform duration-300 group-hover:scale-105"
                                />
                            </div>
                            <CardContent class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <Badge variant="secondary" class="font-normal">
                                        {course.category}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground">
                                        {course.level}
                                    </span>
                                </div>
                                <h3 class="line-clamp-2 text-sm font-semibold leading-snug group-hover:text-primary">
                                    {course.title}
                                </h3>
                                <div
                                    class="flex items-center justify-between text-xs text-muted-foreground"
                                >
                                    <span class="inline-flex items-center gap-1">
                                        <Clock class="size-3.5" />
                                        {course.duration_hours}h
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <Star class="size-3.5 fill-primary text-primary" />
                                        {course.rating.toFixed(1)}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                {/each}
            </div>
        </div>

        <div>
            <h2 class="mb-4 text-lg font-semibold">Recent activity</h2>
            <Card>
                <CardContent class="p-0">
                    <ul class="divide-y">
                        {#each activity as item, i (i)}
                            {@const Icon = activityIcons[item.icon]}
                            <li class="flex items-start gap-3 px-4 py-3">
                                <span
                                    class={cn(
                                        'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full',
                                        item.icon === 'Award'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-primary/15 text-primary',
                                    )}
                                >
                                    <Icon class="size-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm">
                                        <span class="font-medium">{item.label}</span>
                                        <span class="text-muted-foreground"> · {item.detail}</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {item.when}
                                    </p>
                                </div>
                            </li>
                        {/each}
                    </ul>
                </CardContent>
            </Card>
        </div>
    </section>
</div>
