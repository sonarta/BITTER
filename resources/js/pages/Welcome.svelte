<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import ArrowRight from 'lucide-svelte/icons/arrow-right';
    import Check from 'lucide-svelte/icons/check';
    import Clock from 'lucide-svelte/icons/clock';
    import Cloud from 'lucide-svelte/icons/cloud';
    import FlaskConical from 'lucide-svelte/icons/flask-conical';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import Layers from 'lucide-svelte/icons/layers';
    import Monitor from 'lucide-svelte/icons/monitor';
    import Palette from 'lucide-svelte/icons/palette';
    import PlayCircle from 'lucide-svelte/icons/play-circle';
    import Quote from 'lucide-svelte/icons/quote';
    import Server from 'lucide-svelte/icons/server';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Star from 'lucide-svelte/icons/star';
    import Trophy from 'lucide-svelte/icons/trophy';
    import Users from 'lucide-svelte/icons/users';
    import Zap from 'lucide-svelte/icons/zap';
    import AppHead from '@/components/AppHead.svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import { Avatar, AvatarFallback } from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { getInitials } from '@/lib/initials';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login, register } from '@/routes';

    type FeaturedCourse = {
        slug: string;
        title: string;
        instructor: string;
        category: string;
        level: string;
        duration_hours: number;
        rating: number;
        students: number;
        price: number;
        cover: string;
    };

    type CategoryIcon =
        | 'Server'
        | 'Monitor'
        | 'Layers'
        | 'FlaskConical'
        | 'Cloud'
        | 'Palette';

    type Category = { name: string; icon: CategoryIcon; count: number };

    type Testimonial = {
        name: string;
        role: string;
        quote: string;
        rating: number;
    };

    type Stat = { label: string; value: string };

    let {
        canRegister = true,
        featured = [],
        categories = [],
        testimonials = [],
        stats = [],
    }: {
        canRegister: boolean;
        featured: FeaturedCourse[];
        categories: Category[];
        testimonials: Testimonial[];
        stats: Stat[];
    } = $props();

    const auth = $derived(page.props.auth);

    const categoryIcons = {
        Server,
        Monitor,
        Layers,
        FlaskConical,
        Cloud,
        Palette,
    } as const;

    function formatStudents(n: number): string {
        if (n >= 1000) {
            return `${(n / 1000).toFixed(1)}k`;
        }

        return `${n}`;
    }
</script>

<AppHead title="Learn, build, ship." />

<div class="min-h-screen bg-background">
    <!-- Top nav -->
    <header
        class="sticky top-0 z-20 border-b bg-background/80 backdrop-blur"
    >
        <div
            class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4"
        >
            <Link href="/" class="flex items-center gap-2">
                <AppLogo />
            </Link>
            <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
                <Link
                    href="/courses"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                >
                    Courses
                </Link>
                <a
                    href="#categories"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                >
                    Categories
                </a>
                <a
                    href="#why-us"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                >
                    Why us
                </a>
                <a
                    href="#testimonials"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                >
                    Reviews
                </a>
            </nav>
            <div class="flex items-center gap-2">
                {#if auth.user}
                    <Link href={toUrl(dashboard())}>
                        <Button size="sm">Go to Dashboard</Button>
                    </Link>
                {:else}
                    <Link href={toUrl(login())}>
                        <Button variant="ghost" size="sm">Log in</Button>
                    </Link>
                    {#if canRegister}
                        <Link href={toUrl(register())}>
                            <Button size="sm">Sign up free</Button>
                        </Link>
                    {/if}
                {/if}
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative overflow-hidden">
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_30%_10%,--theme(--color-primary/10),transparent_40%),radial-gradient(circle_at_70%_90%,--theme(--color-primary/8),transparent_40%)]"
        ></div>
        <div class="mx-auto max-w-7xl px-4 py-16 sm:py-20 md:py-28">
            <div
                class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:gap-16 lg:items-center"
            >
                <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                    <Badge
                        variant="outline"
                        class="mb-6 w-fit gap-1.5 border-primary/30 bg-primary/5 px-3 py-1 text-xs font-medium text-primary"
                    >
                        <Sparkles class="size-3.5" />
                        New: Svelte 5 Runes course is live
                    </Badge>
                    <h1
                        class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-7xl"
                    >
                        Learn, build,<br />
                        <span class="bg-linear-to-r from-primary to-amber-600 bg-clip-text text-transparent">
                            ship faster.
                        </span>
                    </h1>
                    <p class="mt-6 max-w-lg text-base text-muted-foreground sm:text-lg">
                        A modern learning platform for developers. Master Laravel,
                        Svelte, Tailwind, and more with project-based courses from
                        industry experts.
                    </p>
                    <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row lg:items-start">
                        <Link href="/courses">
                            <Button
                                size="lg"
                                class="h-12 px-8 text-base gap-2 shadow-lg shadow-primary/20"
                            >
                                Browse courses
                                <ArrowRight class="size-4" />
                            </Button>
                        </Link>
                        {#if !auth.user && canRegister}
                            <Link href={toUrl(register())}>
                                <Button
                                    size="lg"
                                    variant="outline"
                                    class="h-12 px-8 text-base"
                                >
                                    Start learning free
                                </Button>
                            </Link>
                        {/if}
                    </div>
                    <ul
                        class="mt-8 flex flex-wrap justify-center gap-x-8 gap-y-2 text-sm text-muted-foreground lg:justify-start"
                    >
                        <li class="inline-flex items-center gap-2">
                            <Check class="size-4 text-primary" />
                            No credit card required
                        </li>
                        <li class="inline-flex items-center gap-2">
                            <Check class="size-4 text-primary" />
                            Cancel anytime
                        </li>
                        <li class="inline-flex items-center gap-2">
                            <Check class="size-4 text-primary" />
                            Certificates included
                        </li>
                    </ul>
                </div>

                <!-- Hero visual -->
                <div class="relative hidden lg:block">
                    <div
                        class="absolute -inset-8 -z-10 rounded-[3rem] bg-linear-to-r from-primary/20 via-primary/10 to-transparent blur-3xl"
                    ></div>
                    <div class="relative">
                        {#if featured[0]}
                            <div
                                class="overflow-hidden rounded-2xl bg-card shadow-2xl shadow-primary/10 rotate-[-1.5deg] border border-primary/10"
                            >
                                <div class="aspect-16/10 overflow-hidden bg-muted">
                                    <img
                                        src={featured[0].cover}
                                        alt={featured[0].title}
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div class="p-5 space-y-3">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <Badge
                                            variant="secondary"
                                            class="font-normal"
                                        >
                                            {featured[0].category}
                                        </Badge>
                                        <span
                                            class="text-xs font-medium text-muted-foreground"
                                        >
                                            {featured[0].instructor}
                                        </span>
                                    </div>
                                    <h3 class="font-semibold text-lg">
                                        {featured[0].title}
                                    </h3>
                                    <div
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <span class="inline-flex items-center gap-1 text-muted-foreground">
                                            <Star class="size-4 fill-primary text-primary" />
                                            {featured[0].rating}
                                        </span>
                                        <span class="text-muted-foreground">
                                            {formatStudents(featured[0].students)} students
                                        </span>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        {#if featured[1]}
                            <div
                                class="absolute -right-8 -bottom-8 w-72 overflow-hidden rounded-xl bg-card shadow-xl shadow-primary/10 rotate-2 border border-primary/10"
                            >
                                <div class="aspect-video overflow-hidden bg-muted">
                                    <img
                                        src={featured[1].cover}
                                        alt={featured[1].title}
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div class="p-4 space-y-2">
                                    <p class="truncate font-semibold text-sm">
                                        {featured[1].title}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {featured[1].instructor}
                                    </p>
                                    <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                        <Star class="size-3 fill-primary text-primary" />
                                        {featured[1].rating}
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <div
                            class="absolute -top-6 -left-6 flex items-center gap-2 rounded-full bg-background border border-primary/20 px-4 py-2.5 shadow-lg shadow-primary/10"
                        >
                            <PlayCircle class="size-5 text-primary" />
                            <span class="text-xs font-medium">
                                4,800+ hours of content
                            </span>
                        </div>
                        <div
                            class="absolute -bottom-4 right-4 flex items-center gap-2 rounded-full bg-primary px-4 py-2.5 shadow-lg shadow-primary/30"
                        >
                            <Check class="size-4 text-primary-foreground" />
                            <span class="text-xs font-medium text-primary-foreground">
                                Free courses available
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats strip -->
        <div class="mx-auto w-full max-w-7xl px-4 pb-16 lg:pb-24">
            <div
                class="grid grid-cols-2 gap-4 rounded-2xl bg-linear-to-br from-primary/5 to-transparent border border-primary/10 p-6 md:grid-cols-4"
            >
                {#each stats as stat (stat.label)}
                    <div>
                        <p class="text-2xl font-bold md:text-3xl">
                            {stat.value}
                        </p>
                        <p class="text-xs text-muted-foreground md:text-sm">
                            {stat.label}
                        </p>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section id="categories" class="border-b py-20">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="mb-10 flex flex-col items-start justify-between gap-3 md:flex-row md:items-end">
                <div>
                    <p class="mb-2 text-sm font-medium text-primary">
                        Explore
                    </p>
                    <h2 class="text-3xl font-bold tracking-tight md:text-4xl">
                        Browse by category
                    </h2>
                    <p class="mt-2 max-w-xl text-sm text-muted-foreground">
                        Pick a topic you're curious about and dive straight in.
                    </p>
                </div>
                <Link
                    href="/courses"
                    class="text-sm font-medium text-primary hover:underline"
                >
                    View all categories →
                </Link>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                {#each categories as category (category.name)}
                    {@const Icon = categoryIcons[category.icon]}
                    <Link
                        href="/courses"
                        class="group rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                        <Card
                            class="h-full transition-all hover:border-primary/40 hover:shadow-lg hover:shadow-primary/5"
                        >
                            <CardContent class="flex flex-col items-start gap-3 py-6">
                                <div
                                    class="flex size-10 items-center justify-center rounded-lg bg-primary/15 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                                >
                                    <Icon class="size-5" />
                                </div>
                                <div>
                                    <p class="font-medium">{category.name}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {category.count} courses
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                {/each}
            </div>
        </div>
    </section>

    <!-- Featured courses -->
    <section class="border-b py-20">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="mb-10 flex flex-col items-start justify-between gap-3 md:flex-row md:items-end">
                <div>
                    <p class="mb-2 text-sm font-medium text-primary">
                        Featured
                    </p>
                    <h2 class="text-3xl font-bold tracking-tight md:text-4xl">
                        Popular right now
                    </h2>
                    <p class="mt-2 max-w-xl text-sm text-muted-foreground">
                        Courses students love this month.
                    </p>
                </div>
                <Link href="/courses">
                    <Button variant="outline" class="gap-1.5">
                        Browse catalog
                        <ArrowRight class="size-4" />
                    </Button>
                </Link>
            </div>
            <div
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                {#each featured as course (course.slug)}
                    <Link
                        href={`/courses/${course.slug}`}
                        class="group rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                        <Card
                            class="flex h-full flex-col overflow-hidden border-transparent bg-card/50 backdrop-blur-sm transition-all hover:bg-card hover:shadow-lg hover:shadow-primary/5"
                        >
                            <div class="relative aspect-video w-full overflow-hidden bg-muted">
                                <img
                                    src={course.cover}
                                    alt={course.title}
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                />
                                {#if course.price === 0}
                                    <span
                                        class="absolute top-3 left-3 rounded-full bg-primary px-2.5 py-0.5 text-xs font-medium text-primary-foreground shadow-sm"
                                    >
                                        Free
                                    </span>
                                {/if}
                            </div>
                            <CardContent class="flex flex-1 flex-col gap-2">
                                <div class="flex items-center gap-2">
                                    <Badge variant="secondary" class="font-normal">
                                        {course.category}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground">
                                        {course.level}
                                    </span>
                                </div>
                                <h3 class="line-clamp-2 font-semibold leading-snug group-hover:text-primary">
                                    {course.title}
                                </h3>
                                <p class="text-xs text-muted-foreground">
                                    by {course.instructor}
                                </p>
                                <div
                                    class="mt-auto flex items-center justify-between border-t pt-3 text-xs text-muted-foreground"
                                >
                                    <span class="inline-flex items-center gap-1">
                                        <Star class="size-3.5 fill-primary text-primary" />
                                        {course.rating}
                                        <span>·</span>
                                        {formatStudents(course.students)}
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <Clock class="size-3.5" />
                                        {course.duration_hours}h
                                    </span>
                                </div>
                                <p class="text-sm font-semibold">
                                    {course.price === 0
                                        ? 'Free'
                                        : `$${course.price}`}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>
                {/each}
            </div>
        </div>
    </section>

    <!-- Why us -->
    <section id="why-us" class="border-b py-20">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <p class="mb-2 text-sm font-medium text-primary">Why LMS App</p>
                <h2 class="text-3xl font-bold tracking-tight md:text-4xl">
                    Built for how developers actually learn
                </h2>
                <p class="mt-3 text-sm text-muted-foreground">
                    Short lessons, real projects, and a community that ships.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                {#each [
                    {
                        icon: Zap,
                        title: 'Project-based',
                        text: 'Every course ends with a real project you can put in your portfolio.',
                    },
                    {
                        icon: Users,
                        title: 'Expert instructors',
                        text: 'Learn from engineers who work with the stack every day — not generic tutors.',
                    },
                    {
                        icon: Trophy,
                        title: 'Verifiable certificates',
                        text: 'Earn certificates that employers can verify on your profile URL.',
                    },
                    {
                        icon: GraduationCap,
                        title: 'Beginner to advanced',
                        text: 'Start from zero, or skip ahead — learning paths adapt to you.',
                    },
                    {
                        icon: PlayCircle,
                        title: 'Offline-friendly',
                        text: 'Download lessons and keep learning on the move, anywhere.',
                    },
                    {
                        icon: Sparkles,
                        title: 'Always updated',
                        text: 'Courses are refreshed as frameworks evolve. No stale content.',
                    },
                ] as feature (feature.title)}
                    <div
                        class="rounded-2xl border border-primary/10 bg-card/50 backdrop-blur-sm p-6 transition-all hover:border-primary/30 hover:bg-card hover:shadow-lg hover:shadow-primary/5"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-primary/15 text-primary"
                        >
                            <feature.icon class="size-5" />
                        </div>
                        <h3 class="mt-4 font-semibold">{feature.title}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {feature.text}
                        </p>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="border-b py-20">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <p class="mb-2 text-sm font-medium text-primary">Loved by devs</p>
                <h2 class="text-3xl font-bold tracking-tight md:text-4xl">
                    What learners are saying
                </h2>
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                {#each testimonials as testimonial (testimonial.name)}
                    <Card
                        class="flex h-full flex-col border-primary/10 bg-card/50 backdrop-blur-sm transition-all hover:border-primary/30 hover:bg-card hover:shadow-lg hover:shadow-primary/5"
                    >
                        <CardContent class="flex flex-1 flex-col pt-6">
                            <Quote class="size-6 text-primary" />
                            <p class="mt-3 flex-1 text-sm leading-relaxed">
                                "{testimonial.quote}"
                            </p>
                            <div class="mt-4 flex">
                                {#each Array(5) as _, i (i)}
                                    <Star
                                        class={i < testimonial.rating
                                            ? 'size-4 fill-primary text-primary'
                                            : 'size-4 text-muted-foreground/40'}
                                    />
                                {/each}
                            </div>
                            <div class="mt-4 flex items-center gap-3 border-t pt-4">
                                <Avatar class="size-9">
                                    <AvatarFallback
                                        class="bg-primary/15 text-primary"
                                    >
                                        {getInitials(testimonial.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <p class="text-sm font-medium">
                                        {testimonial.name}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {testimonial.role}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                {/each}
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-20">
        <div class="mx-auto w-full max-w-5xl px-4">
            <div
                class="relative overflow-hidden rounded-3xl border border-primary/20 bg-linear-to-br from-primary/10 via-primary/5 to-background px-6 py-16 text-center sm:px-12"
            >
                <div
                    class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_center,var(--color-primary),transparent_60%)]/[20]"
                ></div>
                <h2 class="text-3xl font-bold tracking-tight md:text-4xl">
                    Ready to level up?
                </h2>
                <p class="mx-auto mt-3 max-w-xl text-sm text-muted-foreground md:text-base">
                    Join 25,000+ developers learning on LMS App. Start free,
                    upgrade when you're ready.
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    {#if canRegister && !auth.user}
                        <Link href={toUrl(register())}>
                            <Button
                                size="lg"
                                class="gap-2 shadow-lg shadow-primary/20"
                            >
                                Create free account
                                <ArrowRight class="size-4" />
                            </Button>
                        </Link>
                    {/if}
                    <Link href="/courses">
                        <Button size="lg" variant="outline">
                            Browse courses
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t py-10">
        <div
            class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-4 px-4 md:flex-row"
        >
            <div class="flex items-center gap-2">
                <AppLogo />
            </div>
            <p class="text-xs text-muted-foreground">
                {new Date().getFullYear()} LMS App. Built with Laravel, Inertia &
                Svelte.
            </p>
            <div class="flex gap-4 text-xs text-muted-foreground">
                <button class="text-muted-foreground hover:text-foreground">Privacy</button>
                <button class="text-muted-foreground hover:text-foreground">Terms</button>
                <button class="text-muted-foreground hover:text-foreground">Support</button>
            </div>
        </div>
    </footer>
</div>
