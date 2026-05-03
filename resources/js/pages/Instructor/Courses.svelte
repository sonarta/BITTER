<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Teaching', href: '/instructor' },
            { title: 'My Courses', href: '/instructor/courses' },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import Eye from 'lucide-svelte/icons/eye';
    import MoreHorizontal from 'lucide-svelte/icons/more-horizontal';
    import Pencil from 'lucide-svelte/icons/pencil';
    import Plus from 'lucide-svelte/icons/plus';
    import Search from 'lucide-svelte/icons/search';
    import Star from 'lucide-svelte/icons/star';
    import Upload from 'lucide-svelte/icons/upload';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import InstructorNav from '@/components/InstructorNav.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import { cn } from '@/lib/utils';

    type Course = {
        id: string;
        slug: string;
        title: string;
        status: 'published' | 'draft';
        students: number;
        rating: number;
        revenue: number;
        price: number;
        updated_at: string;
        cover: string;
    };

    let {
        courses = [],
    }: {
        courses: Course[];
    } = $props();

    let query = $state('');
    let statusFilter = $state<'all' | 'published' | 'draft'>('all');

    const filtered = $derived(
        courses.filter((course) => {
            const matchQuery =
                query.trim() === '' ||
                course.title.toLowerCase().includes(query.toLowerCase());
            const matchStatus =
                statusFilter === 'all' || course.status === statusFilter;

            return matchQuery && matchStatus;
        }),
    );

    const counts = $derived({
        all: courses.length,
        published: courses.filter((c) => c.status === 'published').length,
        draft: courses.filter((c) => c.status === 'draft').length,
    });
</script>

<AppHead title="My Courses" />

<InstructorNav active="courses" />

<div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-8">
    <section class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">My Courses</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Manage and publish your courses.
            </p>
        </div>
        <Link href="/instructor/courses/create">
            <Button size="lg" class="gap-2">
                <Plus class="size-4" />
                Create new course
            </Button>
        </Link>
    </section>

    <Card>
        <CardContent class="space-y-4 pt-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="inline-flex items-center rounded-lg bg-muted p-1">
                    {#each [
                        { key: 'all', label: `All (${counts.all})` },
                        { key: 'published', label: `Published (${counts.published})` },
                        { key: 'draft', label: `Drafts (${counts.draft})` },
                    ] as const as tab (tab.key)}
                        <Button
                            variant={statusFilter === tab.key ? 'default' : 'ghost'}
                            size="sm"
                            class="rounded-md"
                            onclick={() => (statusFilter = tab.key)}
                        >
                            {tab.label}
                        </Button>
                    {/each}
                </div>

                <div class="relative w-full sm:w-72">
                    <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        bind:value={query}
                        placeholder="Search courses..."
                        class="pl-9"
                    />
                </div>
            </div>

            {#if filtered.length === 0}
                <div class="flex flex-col items-center gap-2 py-16 text-center">
                    <Search class="size-8 text-muted-foreground" />
                    <p class="text-sm font-medium">No courses match your filters</p>
                </div>
            {:else}
                <div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-xs font-medium text-muted-foreground">
                                <th class="px-3 py-3 font-medium">Course</th>
                                <th class="px-3 py-3 font-medium">Status</th>
                                <th class="px-3 py-3 font-medium">Students</th>
                                <th class="px-3 py-3 font-medium">Rating</th>
                                <th class="px-3 py-3 font-medium">Price</th>
                                <th class="px-3 py-3 font-medium">Revenue</th>
                                <th class="px-3 py-3 font-medium">Updated</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each filtered as course (course.slug)}
                                <tr class="border-b transition-colors hover:bg-accent/30">
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-3">
                                            <img
                                                src={course.cover}
                                                alt={course.title}
                                                class="size-10 shrink-0 rounded-md object-cover"
                                            />
                                            <span class="font-medium">
                                                {course.title}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <Badge
                                            variant="outline"
                                            class={cn(
                                                'font-normal',
                                                course.status === 'published'
                                                    ? 'border-emerald-500/40 text-emerald-700 dark:text-emerald-400'
                                                    : 'border-amber-500/40 text-amber-700 dark:text-amber-400',
                                            )}
                                        >
                                            {course.status === 'published' ? 'Published' : 'Draft'}
                                        </Badge>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center gap-1 text-muted-foreground">
                                            <Users class="size-3.5" />
                                            {course.students.toLocaleString()}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        {#if course.rating > 0}
                                            <span class="inline-flex items-center gap-1">
                                                <Star class="size-3.5 fill-primary text-primary" />
                                                {course.rating.toFixed(1)}
                                            </span>
                                        {:else}
                                            <span class="text-xs text-muted-foreground">—</span>
                                        {/if}
                                    </td>
                                    <td class="px-3 py-3">
                                        {course.price === 0 ? 'Free' : `$${course.price}`}
                                    </td>
                                    <td class="px-3 py-3 font-medium">
                                        ${course.revenue.toLocaleString()}
                                    </td>
                                    <td class="px-3 py-3 text-xs text-muted-foreground">
                                        {course.updated_at}
                                    </td>
                                    <td class="px-3 py-3">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                {#snippet children(props)}
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        class="size-8"
                                                        onclick={props.onclick}
                                                        aria-expanded={props['aria-expanded']}
                                                    >
                                                        <MoreHorizontal class="size-4" />
                                                    </Button>
                                                {/snippet}
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-44">
                                                <DropdownMenuItem>
                                                    <Pencil class="mr-2 size-4" />
                                                    <Link href={`/instructor/courses/${course.id}/edit`}>
                                                        Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem>
                                                    <BookOpen class="mr-2 size-4" />
                                                    <Link href={`/instructor/courses/${course.id}/curriculum`}>
                                                        Curriculum
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem>
                                                    <Eye class="mr-2 size-4" />
                                                    <Link href={`/courses/${course.slug}`}>
                                                        View public page
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                {#if course.status === 'draft'}
                                                    <DropdownMenuItem
                                                        onclick={() => router.post(`/instructor/courses/${course.id}/publish`)}
                                                    >
                                                        <Upload class="mr-2 size-4" />
                                                        Publish
                                                    </DropdownMenuItem>
                                                {:else}
                                                    <DropdownMenuItem
                                                        onclick={() => router.post(`/instructor/courses/${course.id}/unpublish`)}
                                                    >
                                                        Unpublish
                                                    </DropdownMenuItem>
                                                {/if}
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            {/if}
        </CardContent>
    </Card>
</div>
