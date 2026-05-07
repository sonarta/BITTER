<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Courses', href: '/courses' },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Clock from 'lucide-svelte/icons/clock';
    import Search from 'lucide-svelte/icons/search';
    import Star from 'lucide-svelte/icons/star';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import CourseCover from '@/components/CourseCover.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { cn } from '@/lib/utils';
    import type { Course } from '@/types';

    let {
        courses = [],
        categories = [],
        levels = [],
    }: {
        courses: Course[];
        categories: string[];
        levels: string[];
    } = $props();

    let query = $state('');
    let activeCategory = $state<string>('All');
    let activeLevel = $state<string>('All');

    const filtered = $derived(
        courses.filter((course) => {
            const matchQuery =
                query.trim() === '' ||
                course.title.toLowerCase().includes(query.toLowerCase()) ||
                course.tagline.toLowerCase().includes(query.toLowerCase());
            const matchCategory =
                activeCategory === 'All' || course.category === activeCategory;
            const matchLevel =
                activeLevel === 'All' || course.level === activeLevel;

            return matchQuery && matchCategory && matchLevel;
        }),
    );

    function formatStudents(n: number): string {
        if (n >= 1000) {
            return `${(n / 1000).toFixed(1)}k`;
        }

        return `${n}`;
    }
</script>

<AppHead title="Courses" />

<div class="mx-auto w-full max-w-7xl px-4 py-8">
    <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Explore Courses</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Level up your skills with hands-on, project-based courses.
            </p>
        </div>
        <div class="relative w-full md:w-80">
            <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
            <Input
                bind:value={query}
                placeholder="Search courses..."
                class="pl-9"
            />
        </div>
    </header>

    <div class="mb-6 flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-muted-foreground">Category</span>
            <Button
                variant={activeCategory === 'All' ? 'default' : 'outline'}
                size="sm"
                class="h-7 rounded-full"
                onclick={() => (activeCategory = 'All')}
            >
                All
            </Button>
            {#each categories as category (category)}
                <Button
                    variant={activeCategory === category ? 'default' : 'outline'}
                    size="sm"
                    class="h-7 rounded-full"
                    onclick={() => (activeCategory = category)}
                >
                    {category}
                </Button>
            {/each}
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-muted-foreground">Level</span>
            <Button
                variant={activeLevel === 'All' ? 'default' : 'outline'}
                size="sm"
                class="h-7 rounded-full"
                onclick={() => (activeLevel = 'All')}
            >
                All
            </Button>
            {#each levels as level (level)}
                <Button
                    variant={activeLevel === level ? 'default' : 'outline'}
                    size="sm"
                    class="h-7 rounded-full"
                    onclick={() => (activeLevel = level)}
                >
                    {level}
                </Button>
            {/each}
        </div>
    </div>

    {#if filtered.length === 0}
        <Card class="border-dashed">
            <CardContent class="flex flex-col items-center gap-2 py-16 text-center">
                <Search class="size-8 text-muted-foreground" />
                <p class="text-sm font-medium">No courses match your filters</p>
                <p class="text-xs text-muted-foreground">Try clearing the search or choosing another category.</p>
            </CardContent>
        </Card>
    {:else}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {#each filtered as course (course.slug)}
                <Link
                    href={`/courses/${course.slug}`}
                    class="group rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-primary"
                >
                    <Card
                        class="h-full overflow-hidden pt-0 pb-4 transition-shadow hover:shadow-lg"
                    >
                        <div
                            class="relative aspect-video w-full overflow-hidden bg-muted"
                        >
                            <CourseCover
                                src={course.cover}
                                source={course.cover_source}
                                title={course.title}
                                loading="lazy"
                                class="h-full w-full"
                                imgClass="transition-transform duration-300 group-hover:scale-105"
                            />
                            {#if course.price === 0}
                                <Badge
                                    class="absolute top-3 left-3 bg-primary text-primary-foreground"
                                >
                                    Free
                                </Badge>
                            {/if}
                        </div>
                        <CardContent class="space-y-3">
                            <div class="flex items-center gap-2">
                                <Badge variant="secondary" class="font-normal">
                                    {course.category}
                                </Badge>
                                <Badge
                                    variant="outline"
                                    class={cn(
                                        'font-normal',
                                        course.level === 'Beginner' &&
                                            'border-emerald-500/40 text-emerald-700 dark:text-emerald-400',
                                        course.level === 'Intermediate' &&
                                            'border-amber-500/40 text-amber-700 dark:text-amber-400',
                                        course.level === 'Advanced' &&
                                            'border-rose-500/40 text-rose-700 dark:text-rose-400',
                                    )}
                                >
                                    {course.level}
                                </Badge>
                            </div>
                            <div>
                                <h3 class="line-clamp-2 font-semibold leading-snug group-hover:text-primary">
                                    {course.title}
                                </h3>
                                <p class="mt-1 line-clamp-2 text-xs text-muted-foreground">
                                    {course.tagline}
                                </p>
                            </div>
                            <div class="flex items-center justify-between text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1">
                                    <Clock class="size-3.5" />
                                    {course.duration_hours}h
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <Users class="size-3.5" />
                                    {formatStudents(course.students)}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <Star class="size-3.5 fill-primary text-primary" />
                                    {course.rating.toFixed(1)}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs text-muted-foreground">
                                    {course.instructor.name}
                                </span>
                                <span class="text-sm font-semibold">
                                    {course.price === 0 ? 'Free' : `$${course.price}`}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            {/each}
        </div>
    {/if}
</div>
