<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Teaching', href: '/instructor' },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import DollarSign from 'lucide-svelte/icons/dollar-sign';
    import Library from 'lucide-svelte/icons/library';
    import Plus from 'lucide-svelte/icons/plus';
    import Star from 'lucide-svelte/icons/star';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import InstructorNav from '@/components/InstructorNav.svelte';
    import { Avatar, AvatarFallback } from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { getInitials } from '@/lib/initials';
    import { cn } from '@/lib/utils';

    type StatIcon = 'Users' | 'DollarSign' | 'Star' | 'Library';

    type Stat = {
        label: string;
        value: string;
        delta: string;
        trend: 'up' | 'down' | 'neutral';
        icon: StatIcon;
    };

    type RevenuePoint = { label: string; value: number };

    type Enrollment = { name: string; course: string; when: string };

    type TopCourse = {
        slug: string;
        title: string;
        students: number;
        rating: number;
        revenue: number;
        price: number;
        cover: string;
    };

    let {
        stats = [],
        revenue_series = [],
        recent_enrollments = [],
        top_courses = [],
    }: {
        stats: Stat[];
        revenue_series: RevenuePoint[];
        recent_enrollments: Enrollment[];
        top_courses: TopCourse[];
    } = $props();

    const statIcons = {
        Users,
        DollarSign,
        Star,
        Library,
    } as const;

    const maxRevenue = $derived(
        Math.max(...revenue_series.map((p) => p.value), 1),
    );
    const totalRevenue = $derived(
        revenue_series.reduce((sum, p) => sum + p.value, 0),
    );
</script>

<AppHead title="Teaching" />

<InstructorNav active="dashboard" />

<div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-8">
    <!-- Header -->
    <section class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Instructor Dashboard</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Track performance and manage your courses.
            </p>
        </div>
        <Link href="/instructor/courses/create">
            <Button size="lg" class="gap-2">
                <Plus class="size-4" />
                Create new course
            </Button>
        </Link>
    </section>

    <!-- Stats -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {#each stats as stat (stat.label)}
            {@const Icon = statIcons[stat.icon]}
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">
                                {stat.label}
                            </p>
                            <p class="mt-1 text-2xl font-bold">{stat.value}</p>
                            <p
                                class={cn(
                                    'mt-1 inline-flex items-center gap-1 text-xs',
                                    stat.trend === 'up' && 'text-emerald-600 dark:text-emerald-400',
                                    stat.trend === 'down' && 'text-rose-600 dark:text-rose-400',
                                    stat.trend === 'neutral' && 'text-muted-foreground',
                                )}
                            >
                                {#if stat.trend === 'up'}
                                    <TrendingUp class="size-3" />
                                {/if}
                                {stat.delta}
                            </p>
                        </div>
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-primary/15 text-primary"
                        >
                            <Icon class="size-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        {/each}
    </section>

    <!-- Revenue chart + top courses -->
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <Card class="lg:col-span-2">
            <CardHeader class="flex flex-row items-start justify-between">
                <div>
                    <CardTitle>Revenue this week</CardTitle>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Total: <span class="font-medium text-foreground">${totalRevenue.toLocaleString()}</span>
                    </p>
                </div>
                <Badge variant="secondary" class="font-normal">Last 7 days</Badge>
            </CardHeader>
            <CardContent>
                <div class="flex h-48 items-end gap-3">
                    {#each revenue_series as point (point.label)}
                        {@const height = Math.round((point.value / maxRevenue) * 100)}
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <div class="relative flex h-full w-full items-end">
                                <div
                                    class="group w-full rounded-t-md bg-primary/20 transition-all hover:bg-primary/30"
                                    style="height: {height}%"
                                >
                                    <div class="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 rounded-md bg-foreground px-2 py-0.5 text-[10px] font-medium text-background opacity-0 transition-opacity group-hover:opacity-100">
                                        ${point.value}
                                    </div>
                                    <div
                                        class="h-full w-full rounded-t-md bg-linear-to-t from-primary to-primary/60"
                                    ></div>
                                </div>
                            </div>
                            <span class="text-xs text-muted-foreground">
                                {point.label}
                            </span>
                        </div>
                    {/each}
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle>Top courses</CardTitle>
                <Link
                    href="/instructor/courses"
                    class="text-xs font-medium text-primary hover:underline"
                >
                    View all
                </Link>
            </CardHeader>
            <CardContent class="space-y-3">
                {#each top_courses as course (course.slug)}
                    <div class="flex items-center gap-3">
                        <img
                            src={course.cover}
                            alt={course.title}
                            class="size-12 shrink-0 rounded-md object-cover"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">
                                {course.title}
                            </p>
                            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1">
                                    <Users class="size-3" />
                                    {course.students.toLocaleString()}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <Star class="size-3 fill-primary text-primary" />
                                    {course.rating.toFixed(1)}
                                </span>
                            </div>
                        </div>
                        <span class="text-sm font-semibold">
                            ${course.revenue}
                        </span>
                    </div>
                {/each}
            </CardContent>
        </Card>
    </section>

    <!-- Recent enrollments -->
    <section>
        <Card>
            <CardHeader>
                <CardTitle>Recent enrollments</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <ul class="divide-y">
                    {#each recent_enrollments as enrollment, i (i)}
                        <li class="flex items-center gap-3 px-6 py-3">
                            <Avatar class="size-9">
                                <AvatarFallback class="bg-primary/15 text-primary">
                                    {getInitials(enrollment.name)}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">
                                    {enrollment.name}
                                </p>
                                <p class="truncate text-xs text-muted-foreground">
                                    enrolled in {enrollment.course}
                                </p>
                            </div>
                            <span class="shrink-0 text-xs text-muted-foreground">
                                {enrollment.when}
                            </span>
                        </li>
                    {/each}
                </ul>
            </CardContent>
        </Card>
    </section>
</div>
