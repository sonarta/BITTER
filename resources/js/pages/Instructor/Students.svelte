<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Teaching', href: '/instructor' },
            { title: 'Students', href: '/instructor/students' },
        ],
    };
</script>

<script lang="ts">
    import Search from 'lucide-svelte/icons/search';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import InstructorNav from '@/components/InstructorNav.svelte';
    import { Avatar, AvatarFallback } from '@/components/ui/avatar';
    import { Card, CardContent } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { getInitials } from '@/lib/initials';

    type Enrollment = {
        id: string;
        student: { name: string; email: string };
        course: { title: string; slug: string | null };
        enrolled_at: string | null;
    };

    let {
        enrollments = [],
    }: {
        enrollments: Enrollment[];
    } = $props();

    let query = $state('');

    const filtered = $derived(
        enrollments.filter((enrollment) => {
            const q = query.trim().toLowerCase();
            if (q === '') {
                return true;
            }

            return (
                enrollment.student.name.toLowerCase().includes(q) ||
                enrollment.student.email.toLowerCase().includes(q) ||
                enrollment.course.title.toLowerCase().includes(q)
            );
        }),
    );
</script>

<AppHead title="Students" />

<InstructorNav active="students" />

<div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-8">
    <section class="flex flex-col gap-2">
        <h1 class="text-3xl font-bold tracking-tight">Students</h1>
        <p class="text-sm text-muted-foreground">
            View students enrolled in your courses.
        </p>
    </section>

    <Card>
        <CardContent class="space-y-4 pt-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Users class="size-4" />
                    <span>{filtered.length.toLocaleString()} enrollments</span>
                </div>

                <div class="relative w-full sm:w-80">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        bind:value={query}
                        placeholder="Search students or courses..."
                        class="pl-9"
                    />
                </div>
            </div>

            {#if filtered.length === 0}
                <div class="flex flex-col items-center gap-2 py-16 text-center">
                    <Search class="size-8 text-muted-foreground" />
                    <p class="text-sm font-medium">No students found</p>
                    <p class="text-xs text-muted-foreground">
                        Try a different search term.
                    </p>
                </div>
            {:else}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-xs font-medium text-muted-foreground"
                            >
                                <th class="px-3 py-3 font-medium">Student</th>
                                <th class="px-3 py-3 font-medium">Email</th>
                                <th class="px-3 py-3 font-medium">Course</th>
                                <th class="px-3 py-3 font-medium">Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each filtered as enrollment (enrollment.id)}
                                <tr
                                    class="border-b transition-colors hover:bg-accent/30"
                                >
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-3">
                                            <Avatar class="size-9">
                                                <AvatarFallback>
                                                    {getInitials(
                                                        enrollment.student.name,
                                                    )}
                                                </AvatarFallback>
                                            </Avatar>
                                            <span class="font-medium">
                                                {enrollment.student.name}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-muted-foreground">
                                        {enrollment.student.email}
                                    </td>
                                    <td class="px-3 py-3">
                                        {enrollment.course.title}
                                    </td>
                                    <td class="px-3 py-3 text-xs text-muted-foreground">
                                        {enrollment.enrolled_at ?? '—'}
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

