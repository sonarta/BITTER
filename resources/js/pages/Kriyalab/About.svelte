<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import FlaskConical from 'lucide-svelte/icons/flask-conical';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import Palette from 'lucide-svelte/icons/palette';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Target from 'lucide-svelte/icons/target';
    import Users from 'lucide-svelte/icons/users';
    import type { ComponentType, SvelteComponent } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import KriyalabFooter from '@/components/Kriyalab/KriyalabFooter.svelte';
    import KriyalabHeader from '@/components/Kriyalab/KriyalabHeader.svelte';
    import { Button } from '@/components/ui/button';

    type LearningPhase = {
        phase: string;
        subtitle: string;
        icon: string;
        description: string;
    };

    let { learningModel }: { learningModel: LearningPhase[] } = $props();

    const iconMap: Record<string, ComponentType<SvelteComponent>> = {
        BookOpen,
        Users,
        Sparkles,
    };
</script>

<AppHead title="Tentang Kami - KRIYALAB" />

<div class="flex min-h-screen flex-col bg-slate-50 text-slate-900">
    <KriyalabHeader />

    <!-- Hero -->
    <section
        class="relative overflow-hidden bg-linear-to-br from-amber-500 via-yellow-500 to-orange-500 text-white"
    >
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.15),transparent_40%)]"
        ></div>
        <div class="mx-auto w-full max-w-5xl px-4 py-16 text-center md:py-20">
            <h1 class="text-3xl font-bold md:text-5xl">Tentang KRIYALAB</h1>
            <p class="mx-auto mt-4 max-w-2xl text-sm text-white/80 md:text-base">
                Inkubator Bisnis & Kewirausahaan Kreatif untuk mahasiswa
                Pendidikan Kriya.
            </p>
        </div>
    </section>

    <!-- Vision -->
    <section class="py-16 md:py-20">
        <div class="mx-auto grid w-full max-w-6xl gap-10 px-4 md:grid-cols-2 md:items-center">
            <div>
                <p class="text-sm font-semibold text-amber-600">Visi Kami</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Mengubah karya seni menjadi peluang usaha nyata
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-slate-600 md:text-base">
                    KRIYALAB adalah platform inkubator bisnis digital yang
                    dirancang khusus untuk mahasiswa Pendidikan Kriya. Dengan
                    mengintegrasikan Model Pembelajaran FLIS-FREAN, kami
                    membantu mahasiswa menumbuhkan mindset wirausaha sejak di
                    bangku kuliah.
                </p>
                <p class="mt-3 text-sm leading-relaxed text-slate-600 md:text-base">
                    Sistem pembelajaran mandiri, interaktif, dan kolaboratif
                    memungkinkan setiap mahasiswa mengembangkan produk kreatif
                    dengan nilai jual tinggi berbasis potensi lokal.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                {#each [
                    { icon: Palette, label: 'Kreativitas Seni', desc: 'Berbasis kriya & budaya lokal' },
                    { icon: Target, label: 'Orientasi Bisnis', desc: 'Model bisnis yang terukur' },
                    { icon: Users, label: 'Kolaboratif', desc: 'Pembelajaran antar mahasiswa' },
                    { icon: GraduationCap, label: 'Akademik', desc: 'Terintegrasi dengan kurikulum' },
                ] as item (item.label)}
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600"
                        >
                            <item.icon class="size-5" />
                        </div>
                        <p class="mt-3 text-sm font-bold">{item.label}</p>
                        <p class="mt-1 text-xs text-slate-600">{item.desc}</p>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- FLIS-FREAN -->
    <section class="bg-white py-16 md:py-20">
        <div class="mx-auto w-full max-w-7xl px-4">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <p class="text-sm font-semibold text-amber-600">
                    Pendekatan Pembelajaran
                </p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Model FLIS-FREAN
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Flipped Learning yang dipadu dengan Problem-Based Learning
                    untuk hasil pembelajaran yang lebih bermakna.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                {#each learningModel as phase, idx (phase.phase)}
                    {@const Icon = iconMap[phase.icon] ?? BookOpen}
                    <div
                        class="relative overflow-hidden rounded-2xl border border-amber-100 bg-linear-to-br from-amber-50 to-white p-6"
                    >
                        <span
                            class="absolute top-4 right-5 text-5xl font-black text-amber-100 select-none"
                        >
                            0{idx + 1}
                        </span>
                        <div
                            class="relative flex size-12 items-center justify-center rounded-xl bg-linear-to-br from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-500/20"
                        >
                            <Icon class="size-6" />
                        </div>
                        <h3 class="relative mt-5 text-lg font-bold">
                            {phase.phase}
                        </h3>
                        <p class="relative text-xs font-medium text-amber-600">
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

    <!-- Research -->
    <section class="py-16 md:py-20">
        <div class="mx-auto w-full max-w-5xl px-4">
            <div
                class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm md:p-12"
            >
                <div class="flex flex-col items-start gap-6 md:flex-row md:items-center">
                    <div
                        class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600"
                    >
                        <FlaskConical class="size-8" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-amber-600">
                            Metodologi Penelitian
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">
                            Penelitian R&D Model ADDIE
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            KRIYALAB dikembangkan melalui pendekatan Research &
                            Development (R&D) dengan Model ADDIE — Analysis,
                            Design, Development, Implementation, Evaluation —
                            untuk memastikan setiap komponen platform
                            memberikan dampak pembelajaran yang terukur.
                        </p>
                        <p class="mt-2 text-xs text-slate-500">
                            Dokumen disusun berdasarkan RPS Business and
                            Entrepreneurship & Panduan LKM Mahasiswa.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="pb-16">
        <div class="mx-auto w-full max-w-3xl px-4 text-center">
            <h2 class="text-2xl font-bold md:text-3xl">
                Tertarik untuk berkolaborasi?
            </h2>
            <p class="mt-3 text-sm text-slate-600">
                Hubungi tim peneliti kami untuk informasi lebih lanjut.
            </p>
            <div class="mt-6">
                <Link href="/kriyalab/kontak">
                    <Button
                        size="lg"
                        class="h-12 rounded-full bg-amber-500 px-8 text-base font-semibold text-white hover:bg-amber-600"
                    >
                        Hubungi Kami
                    </Button>
                </Link>
            </div>
        </div>
    </section>

    <!-- Spacer for mobile bottom nav -->
    <div class="h-16 md:hidden"></div>

    <KriyalabFooter />
</div>
