<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Teaching', href: '/instructor' },
            { title: 'My Courses', href: '/instructor/courses' },
            { title: 'Course Form', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import Save from 'lucide-svelte/icons/save';
    import AppHead from '@/components/AppHead.svelte';
    import CourseCover from '@/components/CourseCover.svelte';
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
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select';
    import { Textarea } from '@/components/ui/textarea';

    type CourseData = {
        id?: string;
        title: string;
        slug: string;
        tagline: string;
        description: string;
        category: string;
        level: string;
        cover_url: string;
        price: number;
    } | null;

    import { untrack } from 'svelte';

    let {
        course = null,
        categories = [],
    }: {
        course: CourseData;
        categories: string[];
    } = $props();

    const isEditing = $derived(!!course?.id);
    const pageTitle = $derived(isEditing ? 'Edit Course' : 'Create Course');

    const LEGACY_DEFAULT_COVER_MARKER =
        'images.unsplash.com/photo-1555066931-4365d14bab8c';
    const PLACEHOLDER_COVER =
        'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPSc4MDAnIGhlaWdodD0nNDUwJyB2aWV3Qm94PScwIDAgODAwIDQ1MCc+PGRlZnM+PGxpbmVhckdyYWRpZW50IGlkPSdnJyB4MT0nMCcgeTE9JzAnIHgyPScxJyB5Mj0nMSc+PHN0b3Agb2Zmc2V0PScwJyBzdG9wLWNvbG9yPScjMGYxNzJhJy8+PHN0b3Agb2Zmc2V0PScxJyBzdG9wLWNvbG9yPScjMWQ0ZWQ4Jy8+PC9saW5lYXJHcmFkaWVudD48L2RlZnM+PHJlY3Qgd2lkdGg9JzgwMCcgaGVpZ2h0PSc0NTAnIGZpbGw9J3VybCgjZyknLz48dGV4dCB4PSc1MCUnIHk9JzUwJScgZG9taW5hbnQtYmFzZWxpbmU9J21pZGRsZScgdGV4dC1hbmNob3I9J21pZGRsZScgZm9udC1mYW1pbHk9J3N5c3RlbS11aSwgLWFwcGxlLXN5c3RlbSwgU2Vnb2UgVUksIFJvYm90bywgQXJpYWwnIGZvbnQtc2l6ZT0nNDgnIGZpbGw9JyNmZmZmZmYnIG9wYWNpdHk9JzAuOTInPk5vIENvdmVyPC90ZXh0Pjwvc3ZnPg==';

    const form = useForm(untrack(() => ({
        title: course?.title ?? '',
        slug: course?.slug ?? '',
        tagline: course?.tagline ?? '',
        description: course?.description ?? '',
        category: course?.category ?? categories[0] ?? '',
        level: course?.level ?? 'Beginner',
        cover_url: course?.cover_url ?? '',
        price: course?.price ?? 0,
    })));

    const coverSource = $derived(
        form.cover_url.trim() === '' ||
            form.cover_url.includes(LEGACY_DEFAULT_COVER_MARKER)
            ? 'placeholder'
            : 'manual',
    );
    const coverPreviewSrc = $derived(
        coverSource === 'manual' ? form.cover_url : PLACEHOLDER_COVER,
    );

    function generateSlug(title: string): string {
        return title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }

    function handleTitleInput() {
        if (!isEditing) {
            form.slug = generateSlug(form.title);
        }
    }

    function submit(e: SubmitEvent) {
        e.preventDefault();

        if (isEditing && course?.id) {
            form.put(`/instructor/courses/${course.id}`);
        } else {
            form.post('/instructor/courses');
        }
    }
</script>

<AppHead title={pageTitle} />

<InstructorNav active="courses" />

<div class="mx-auto w-full max-w-3xl space-y-6 px-4 py-8">
    <section>
        <h1 class="text-3xl font-bold tracking-tight">{pageTitle}</h1>
        <p class="mt-1 text-sm text-muted-foreground">
            {isEditing
                ? 'Update the course details below.'
                : 'Fill in the details to create a new course.'}
        </p>
    </section>

    <form onsubmit={submit}>
        <Card>
            <CardHeader>
                <CardTitle>Course Details</CardTitle>
            </CardHeader>
            <CardContent class="space-y-5">
                <div class="space-y-2">
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        bind:value={form.title}
                        oninput={handleTitleInput}
                        placeholder="e.g. Konsep Dasar Kewirausahaan"
                    />
                    <InputError message={form.errors.title} />
                </div>

                <div class="space-y-2">
                    <Label for="slug">Slug</Label>
                    <Input
                        id="slug"
                        bind:value={form.slug}
                        placeholder="konsep-dasar-kewirausahaan"
                    />
                    <InputError message={form.errors.slug} />
                </div>

                <div class="space-y-2">
                    <Label for="tagline">Tagline</Label>
                    <Input
                        id="tagline"
                        bind:value={form.tagline}
                        placeholder="Short description..."
                    />
                    <InputError message={form.errors.tagline} />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        bind:value={form.description}
                        rows={5}
                        placeholder="Full course description..."
                    />
                    <InputError message={form.errors.description} />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="category">Category</Label>
                        <Select
                            type="single"
                            value={form.category}
                            onValueChange={(v) => {
                                if (v) {
                                    form.category = v;
                                }
                            }}
                        >
                            <SelectTrigger id="category">
                                <SelectValue placeholder="Select category" />
                            </SelectTrigger>
                            <SelectContent>
                                {#each categories as cat (cat)}
                                    <SelectItem value={cat}>{cat}</SelectItem>
                                {/each}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.category} />
                    </div>

                    <div class="space-y-2">
                        <Label for="level">Level</Label>
                        <Select
                            type="single"
                            value={form.level}
                            onValueChange={(v) => {
                                if (v) {
                                    form.level = v;
                                }
                            }}
                        >
                            <SelectTrigger id="level">
                                <SelectValue placeholder="Select level" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Beginner"
                                    >Beginner</SelectItem
                                >
                                <SelectItem value="Intermediate"
                                    >Intermediate</SelectItem
                                >
                                <SelectItem value="Advanced"
                                    >Advanced</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.level} />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="cover_url">Cover Image URL</Label>
                        <Input
                            id="cover_url"
                            type="url"
                            bind:value={form.cover_url}
                            placeholder="https://..."
                        />
                        <InputError message={form.errors.cover_url} />
                        <div class="mt-3">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs font-medium text-muted-foreground">
                                    Cover preview
                                </p>
                                <Badge
                                    variant={coverSource === 'manual' ? 'outline' : 'secondary'}
                                    class="font-normal"
                                >
                                    {coverSource === 'manual'
                                        ? 'Manual cover'
                                        : 'Default cover'}
                                </Badge>
                            </div>
                            <div class="aspect-video overflow-hidden rounded-lg border bg-muted">
                                <CourseCover
                                    src={coverPreviewSrc}
                                    source={coverSource}
                                    title={form.title || 'Course cover'}
                                    loading="lazy"
                                    showBadge={false}
                                    class="h-full w-full"
                                />
                            </div>
                            <p class="mt-2 text-xs text-muted-foreground">
                                Leave empty to use the system default cover.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="price">Price (Rp)</Label>
                        <Input
                            id="price"
                            type="number"
                            bind:value={form.price}
                            min="0"
                        />
                        <InputError message={form.errors.price} />
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <Button type="submit" disabled={form.processing}>
                        <Save class="mr-2 size-4" />
                        {form.processing ? 'Saving...' : 'Save Course'}
                    </Button>
                </div>
            </CardContent>
        </Card>
    </form>
</div>
