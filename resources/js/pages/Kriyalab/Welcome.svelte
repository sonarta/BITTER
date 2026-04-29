<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowRight from 'lucide-svelte/icons/arrow-right';
    import Award from 'lucide-svelte/icons/award';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import CheckCircle2 from 'lucide-svelte/icons/check-circle-2';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Lightbulb from 'lucide-svelte/icons/lightbulb';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Target from 'lucide-svelte/icons/target';
    import Users from 'lucide-svelte/icons/users';
    import type { ComponentType, SvelteComponent } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import KriyalabFooter from '@/components/Kriyalab/KriyalabFooter.svelte';
    import KriyalabHeader from '@/components/Kriyalab/KriyalabHeader.svelte';
    import { Button } from '@/components/ui/button';

    type Module = {
        number: string;
        title: string;
        icon: string;
        items: string[];
    };

    type LearningPhase = {
        phase: string;
        subtitle: string;
        icon: string;
        description: string;
    };

    type Assessment = { label: string; value: number };

    let {
        modules,
        assessment,
        learningModel,
    }: {
        modules: Module[];
        assessment: Assessment[];
        learningModel: LearningPhase[];
    } = $props();

    const iconMap: Record<string, ComponentType<SvelteComponent>> = {
        Lightbulb,
        LayoutGrid,
        Target,
        Award,
        BookOpen,
        Users,
        Sparkles,
    };
</script>

<AppHead title="KRIYALAB - Inkubator Bisnis Kreatif" />

<div class="flex min-h-screen flex-col bg-slate-50 text-slate-900">
    <KriyalabHeader />

    <!-- Hero -->
    <section
        class="relative overflow-hidden bg-linear-to-br from-indigo-700 via-indigo-600 to-purple-600 text-white"
    >
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_30%,rgba(255,255,255,0.15),transparent_50%),radial-gradient(circle_at_80%_70%,rgba(168,85,247,0.25),transparent_50%)]"
        ></div>
        <div
            class="mx-auto flex w-full max-w-6xl flex-col items-center px-4 py-16 text-center md:py-24 lg:py-32"
        >
            <span
                class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold backdrop-blur-sm"
            >
                <Sparkles class="size-4" />
                Model Pembelajaran FLIS-FREAN
            </span>
            <h1
                class="max-w-4xl text-3xl font-bold leading-tight md:text-4xl lg:text-5xl xl:text-6xl"
            >
                Bangun Masa Depan
                <span class="block text-amber-300">
                    Bisnis Kreatif Anda di KRIYALAB
                </span>
            </h1>
            <div class="mt-6 max-w-2xl space-y-3">
                <p
                    class="text-sm font-medium text-white/90 md:text-base lg:text-lg"
                >
                    Platform Inkubator Bisnis Digital Mahasiswa Pendidikan Kriya
                    berbasis Model Pembelajaran FLIS-FREAN.
                </p>
                <p class="text-sm text-white/80 md:text-base">
                    Ubah karya seni dan kreativitas Anda menjadi peluang usaha
                    yang nyata melalui sistem pembelajaran mandiri, interaktif,
                    dan kolaboratif.
                </p>
            </div>
            <div
                class="mt-10 flex flex-col items-center gap-4 sm:flex-row md:gap-5"
            >
                <Link href="/login">
                    <Button
                        size="lg"
                        class="h-13 gap-2 rounded-full bg-amber-400 px-8 text-base font-semibold text-slate-900 shadow-xl shadow-amber-400/30 hover:bg-amber-300"
                    >
                        Masuk Ke LMS
                        <ArrowRight class="size-5" />
                    </Button>
                </Link>
                <Link href="/kriyalab/tentang">
                    <Button
                        size="lg"
                        variant="outline"
                        class="h-13 rounded-full border-white/40 bg-white/10 px-6 text-base font-medium text-white backdrop-blur-sm hover:bg-white/20 hover:text-white"
                    >
                        Pelajari Lebih Lanjut
                    </Button>
                </Link>
            </div>
        </div>
    </section>

    <!-- Katalog Materi: 4 Box -->
    <section class="py-16 md:py-20">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <p class="text-sm font-semibold text-indigo-600">
                    Katalog Materi
                </p>
                <h2
                    class="mt-2 text-3xl font-bold tracking-tight md:text-4xl"
                >
                    Struktur Pembelajaran Terpadu
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    15 pertemuan terbagi dalam 3 modul utama, dilengkapi sistem
                    penilaian yang komprehensif.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                {#each modules as mod, idx (mod.number)}
                    {@const Icon = iconMap[mod.icon] ?? BookOpen}
                    <article
                        class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5"
                    >
                        <div
                            class="absolute top-0 right-0 text-[120px] font-black leading-none text-slate-100 opacity-50 select-none"
                        >
                            {idx + 1}
                        </div>
                        <div class="relative">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"
                                >
                                    <Icon class="size-5" />
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wider text-indigo-600"
                                    >
                                        {mod.number}
                                    </p>
                                    <h3 class="text-lg font-bold">
                                        {mod.title}
                                    </h3>
                                </div>
                            </div>
                            <ul class="mt-5 space-y-2 border-t border-slate-100 pt-4">
                                {#each mod.items as item (item)}
                                    <li
                                        class="flex items-start gap-2 text-sm text-slate-700"
                                    >
                                        <CheckCircle2
                                            class="mt-0.5 size-4 shrink-0 text-indigo-500"
                                        />
                                        <span>{item}</span>
                                    </li>
                                {/each}
                            </ul>
                        </div>
                    </article>
                {/each}
            </div>

            <!-- Assessment breakdown bar -->
            <div
                class="mt-12 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8"
            >
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="flex size-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"
                    >
                        <Award class="size-5" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-indigo-600">
                            Bobot Penilaian
                        </p>
                        <p class="text-sm font-semibold">
                            Total komponen evaluasi: 100%
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                    {#each assessment as item (item.label)}
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center"
                        >
                            <p class="text-2xl font-bold text-indigo-600">
                                {item.value}%
                            </p>
                            <p class="mt-1 text-xs text-slate-600">
                                {item.label}
                            </p>
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </section>

    <!-- FLIS-FREAN Learning Model -->
    <section class="bg-white py-16 md:py-20">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <p class="text-sm font-semibold text-indigo-600">
                    Model Pembelajaran
                </p>
                <h2
                    class="mt-2 text-3xl font-bold tracking-tight md:text-4xl"
                >
                    FLIS-FREAN Learning Flow
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Tiga fase pembelajaran yang saling terintegrasi untuk hasil
                    optimal.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                {#each learningModel as phase, idx (phase.phase)}
                    {@const Icon = iconMap[phase.icon] ?? BookOpen}
                    <div
                        class="relative overflow-hidden rounded-2xl border border-indigo-100 bg-linear-to-br from-indigo-50 to-white p-6"
                    >
                        <span
                            class="absolute top-4 right-5 text-5xl font-black text-indigo-100 select-none"
                        >
                            0{idx + 1}
                        </span>
                        <div
                            class="relative flex size-12 items-center justify-center rounded-xl bg-linear-to-br from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/20"
                        >
                            <Icon class="size-6" />
                        </div>
                        <h3 class="relative mt-5 text-lg font-bold">
                            {phase.phase}
                        </h3>
                        <p class="relative text-xs font-medium text-indigo-600">
                            {phase.subtitle}
                        </p>
                        <p class="relative mt-3 text-sm leading-relaxed text-slate-600">
                            {phase.description}
                        </p>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- CTA strip -->
    <section class="pb-16">
        <div class="mx-auto w-full max-w-5xl px-4">
            <div
                class="relative overflow-hidden rounded-3xl bg-linear-to-br from-indigo-700 via-indigo-600 to-purple-600 px-6 py-12 text-center text-white shadow-xl md:px-12"
            >
                <div
                    class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_30%_30%,rgba(255,255,255,0.15),transparent_50%)]"
                ></div>
                <h2 class="text-2xl font-bold md:text-3xl">
                    Siap memulai perjalanan bisnis kreatif Anda?
                </h2>
                <p class="mx-auto mt-3 max-w-xl text-sm text-white/80 md:text-base">
                    Akses seluruh materi pembelajaran melalui LMS KRIYALAB.
                </p>
                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <Link href="/login">
                        <Button
                            size="lg"
                            class="h-12 gap-2 rounded-full bg-amber-400 px-8 text-base font-semibold text-slate-900 hover:bg-amber-300"
                        >
                            Masuk Ke LMS
                            <ArrowRight class="size-4" />
                        </Button>
                    </Link>
                    <Link href="/kriyalab/kontak">
                        <Button
                            size="lg"
                            variant="outline"
                            class="h-12 rounded-full border-white/40 bg-white/10 px-6 text-white hover:bg-white/20 hover:text-white"
                        >
                            Hubungi Tim Peneliti
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <!-- Spacer for mobile bottom nav -->
    <div class="h-16 md:hidden"></div>

    <KriyalabFooter />
</div>
