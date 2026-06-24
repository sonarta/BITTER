<script lang="ts">
    import BookOpen from 'lucide-svelte/icons/book-open';
    import CheckCircle2 from 'lucide-svelte/icons/check-circle-2';
    import FileText from 'lucide-svelte/icons/file-text';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import Lightbulb from 'lucide-svelte/icons/lightbulb';
    import Rocket from 'lucide-svelte/icons/rocket';
    import Shield from 'lucide-svelte/icons/shield';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Target from 'lucide-svelte/icons/target';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import BiterFooter from '@/components/Biter/BiterFooter.svelte';
    import BiterHeader from '@/components/Biter/BiterHeader.svelte';
    import { Button } from '@/components/ui/button';

    type Researcher = {
        type: 'lead' | 'researcher';
        name: string;
        role: string;
        institution: string;
        expertise: string;
        email: string | null;
        department: string;
        photo: string | null;
        photo_alt: string;
        photo_status: 'ready' | 'pending';
    };

    type ResearchPartner = {
        name: string;
        title: string;
        photo: string | null;
        photo_alt: string;
    };

    let {
        researchers,
        researchPartners,
    }: {
        researchers: Researcher[];
        researchPartners: ResearchPartner[];
    } = $props();

    const addieSteps = [
        {
            code: 'A',
            label: 'Analyze',
            title: 'Analisis',
            description:
                'Identifikasi kebutuhan mahasiswa Pendidikan Kriya terhadap literasi bisnis dan analisis infrastruktur pembelajaran digital yang tersedia.',
        },
        {
            code: 'D',
            label: 'Design',
            title: 'Desain',
            description:
                'Perancangan kurikulum kewirausahaan kreatif dan desain sistem pembelajaran digital yang mengintegrasikan sintaks model FLIS-FREAN.',
        },
        {
            code: 'D',
            label: 'Develop',
            title: 'Pengembangan',
            description:
                'Pembuatan prototipe platform BITER, pengembangan modul digital, dan validasi oleh ahli materi serta ahli media untuk memastikan kualitas sistem.',
        },
        {
            code: 'I',
            label: 'Implement',
            title: 'Implementasi',
            description:
                'Uji coba penggunaan sistem dalam mata kuliah Business and Entrepreneurship di Program Studi Pendidikan Kriya untuk melihat efektivitas praktisnya.',
        },
        {
            code: 'E',
            label: 'Evaluate',
            title: 'Evaluasi',
            description:
                'Mengukur peningkatan kompetensi mahasiswa melalui evaluasi formatif dan sumatif yang mencakup aspek kognitif dan keterampilan bisnis.',
        },
    ];

    const outcomes = [
        {
            icon: Rocket,
            title: 'Sistem BITER',
            description:
                'Platform web-learning yang valid dan efektif untuk pembelajaran kewirausahaan seni.',
        },
        {
            icon: BookOpen,
            title: 'Modul & LKM Digital',
            description:
                'Lembar Kerja Mahasiswa berbasis model FLIS-FREAN yang menunjang pembelajaran mandiri.',
        },
        {
            icon: FileText,
            title: 'Publikasi Ilmiah',
            description:
                'Artikel hasil penelitian yang dipublikasikan pada Jurnal Internasional/Nasional bereputasi.',
        },
        {
            icon: Shield,
            title: 'Hak Kekayaan Intelektual (HKI)',
            description:
                'Perlindungan atas karya digital dan inovasi sistem pembelajaran yang dikembangkan.',
        },
    ];

    function getInitials(name: string): string {
        return name
            .split(' ')
            .filter(Boolean)
            .slice(0, 2)
            .map((word) => word[0]?.toUpperCase() ?? '')
            .join('');
    }

    const leadResearcher = $derived(
        researchers.find((person) => person.type === 'lead') ?? null,
    );

    const supportingResearchers = $derived(
        researchers.filter((person) => person.type !== 'lead'),
    );
</script>

<AppHead title="Tentang Kami - BITER" />

<div class="flex min-h-screen flex-col bg-slate-50 text-slate-900">
    <BiterHeader />

    <!-- Hero -->
    <section
        class="relative overflow-hidden border-b border-slate-200 bg-white md:border-b-0 md:bg-linear-to-br md:from-[#1964af] md:to-[#175a93]"
    >
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_0%,rgba(25,100,175,0.08),transparent_60%)] md:bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.18),transparent_45%)]"
        ></div>
        <div class="mx-auto w-full max-w-5xl px-4 py-16 text-center md:py-20">
            <p class="text-sm font-semibold text-[#1964af] md:text-white/70">
                Tentang Kami
            </p>
            <h1
                class="mt-2 text-3xl font-bold text-slate-900 md:text-5xl md:text-white"
            >
                Mengenal Lebih Dekat <span
                    class="text-[#1964af] md:text-[#cbe4f8]">BITER</span
                >
            </h1>
            <p
                class="mx-auto mt-4 max-w-2xl text-sm text-slate-600 md:text-base md:text-white/85"
            >
                Mengenal lebih dekat tentang penelitian dan pengembangan sistem
                BITER.
            </p>
        </div>
    </section>

    <!-- Tujuan Penelitian -->
    <section class="py-16 md:py-20">
        <div
            class="mx-auto grid w-full max-w-6xl gap-10 px-4 md:grid-cols-2 md:items-center"
        >
            <div>
                <p class="text-sm font-semibold text-[#1964af]">
                    Tujuan Penelitian
                </p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Mengembangkan Kompetensi Bisnis Kreatif Mahasiswa
                </h2>
                <p
                    class="mt-4 text-sm leading-relaxed text-slate-600 md:text-base"
                >
                    Penelitian ini bertujuan untuk mengembangkan sistem
                    pembelajaran kewirausahaan berbasis web-learning (BITER)
                    sebagai solusi inovatif dalam meningkatkan kompetensi bisnis
                    kreatif mahasiswa. Fokus utama adalah membekali mahasiswa
                    dengan kemampuan merancang model bisnis yang adaptif dan
                    orisinal berbasis potensi lokal.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                {#each [{ icon: Lightbulb, label: 'Inovatif', desc: 'Solusi pembelajaran berbasis web-learning' }, { icon: Target, label: 'Berorientasi Hasil', desc: 'Kompetensi bisnis terukur' }, { icon: Users, label: 'Kolaboratif', desc: 'Potensi lokal berbasis komunitas' }, { icon: GraduationCap, label: 'Akademik', desc: 'Terintegrasi dengan kurikulum kriya' }] as item (item.label)}
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-lg bg-[#e8f2fc] text-[#1964af]"
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

    <!-- Visi BITER -->
    <section class="bg-white py-16 md:py-20">
        <div class="mx-auto w-full max-w-5xl px-4">
            <div
                class="relative overflow-hidden rounded-3xl border border-[#e8f2fc] bg-linear-to-br from-[#f0f7ff] to-white p-8 md:p-12"
            >
                <div
                    class="absolute top-0 right-0 text-[180px] font-black leading-none text-[#e8f2fc] opacity-60 select-none"
                >
                    V
                </div>
                <div class="relative">
                    <div
                        class="mb-5 flex size-12 items-center justify-center rounded-xl bg-[#1964af] text-white shadow-md shadow-[#1964af]/20"
                    >
                        <Sparkles class="size-6" />
                    </div>
                    <p class="text-sm font-semibold text-[#1964af]">
                        Visi BITER
                    </p>
                    <h2 class="mt-2 text-2xl font-bold md:text-3xl">
                        Artpreneur di Lingkungan Kampus Seni
                    </h2>
                    <p
                        class="mt-4 max-w-3xl text-sm leading-relaxed text-slate-600 md:text-base"
                    >
                        Memberikan pengalaman belajar yang adaptif, mandiri, dan
                        interaktif melalui simulasi model bisnis digital,
                        mentoring, serta ruang kolaborasi yang mendorong
                        tumbuhnya jiwa <span
                            class="font-semibold text-[#1964af]"
                            >artpreneur</span
                        > di lingkungan kampus seni.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Metodologi R&D ADDIE -->
    <section class="py-16 md:py-20">
        <div class="mx-auto w-full max-w-6xl px-4">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <p class="text-sm font-semibold text-[#1964af]">
                    Metodologi Penelitian
                </p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Research & Development — Model ADDIE
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Metode R&D dengan model ADDIE yang terstruktur memastikan
                    setiap komponen platform memberikan dampak pembelajaran yang
                    terukur.
                </p>
            </div>

            <div class="flex flex-col gap-4">
                {#each addieSteps as step (step.label)}
                    <div
                        class="group flex items-start gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:border-[#cbe4f8] hover:shadow-lg hover:shadow-[#1964af]/5"
                    >
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-[#1964af] text-xl font-black text-white shadow-md shadow-[#1964af]/20"
                        >
                            {step.code}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-[#1964af]"
                                >
                                    {step.label}
                                </p>
                                <span class="text-xs text-slate-400">—</span>
                                <p class="text-xs text-slate-500">
                                    {step.title}
                                </p>
                            </div>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-slate-700"
                            >
                                {step.description}
                            </p>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- Luaran Penelitian -->
    <section class="bg-white py-16 md:py-20">
        <div class="mx-auto w-full max-w-6xl px-4">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <p class="text-sm font-semibold text-[#1964af]">
                    Luaran Penelitian
                </p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Target Luaran Strategis
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Penelitian ini ditargetkan menghasilkan berbagai luaran
                    strategis yang berdampak nyata.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                {#each outcomes as item (item.title)}
                    <div
                        class="group flex items-start gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm transition-all hover:border-[#cbe4f8] hover:bg-white hover:shadow-lg hover:shadow-[#1964af]/5"
                    >
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-[#e8f2fc] text-[#1964af]"
                        >
                            <item.icon class="size-5" />
                        </div>
                        <div>
                            <p class="text-sm font-bold">{item.title}</p>
                            <p
                                class="mt-1 text-sm leading-relaxed text-slate-600"
                            >
                                {item.description}
                            </p>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- Dampak & Transformasi -->
    <section class="py-16 md:py-20">
        <div class="mx-auto w-full max-w-6xl px-4">
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Dampak -->
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm md:p-10"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-xl bg-[#e8f2fc] text-[#1964af]"
                    >
                        <CheckCircle2 class="size-6" />
                    </div>
                    <p class="mt-5 text-sm font-semibold text-[#1964af]">
                        Dampak dan Harapan
                    </p>
                    <h3 class="mt-1 text-xl font-bold">
                        Lulusan Siap Jadi Artpreneur
                    </h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        Melalui platform BITER, diharapkan mahasiswa Pendidikan
                        Kriya tidak hanya ahli dalam berkarya seni, tetapi juga
                        percaya diri dan kompeten dalam mengelola bisnis kreatif
                        secara mandiri. Hal ini bertujuan untuk menciptakan
                        lulusan yang siap menjadi wirausaha muda
                        <span class="font-semibold text-[#1964af]"
                            >(artpreneur)</span
                        > yang mampu bersaing di era ekonomi kreatif.
                    </p>
                </div>
                <!-- Transformasi -->
                <div
                    class="relative overflow-hidden rounded-3xl bg-slate-900 p-8 text-white shadow-xl md:p-10"
                >
                    <div
                        class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_30%,rgba(25,100,175,0.3),transparent_55%)]"
                    ></div>
                    <div
                        class="flex size-12 items-center justify-center rounded-xl bg-white/10 text-white backdrop-blur-sm"
                    >
                        <Rocket class="size-6" />
                    </div>
                    <p class="mt-5 text-sm font-semibold text-[#cbe4f8]">
                        Transformasi Bisnis Kreatif
                    </p>
                    <h3 class="mt-1 text-xl font-bold">
                        Katalisator Transformasi Digital
                    </h3>
                    <p class="mt-3 text-sm leading-relaxed text-white/80">
                        BITER hadir sebagai katalisator transformasi digital
                        dalam pendidikan, menghubungkan nilai pendidikan dengan
                        teknologi modern yang relevan dengan perkembangan zaman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tim Peneliti -->
    <section class="bg-white py-16 md:py-20">
        <div class="mx-auto w-full max-w-5xl px-4">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <p class="text-sm font-semibold text-[#1964af]">Tim Peneliti</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Dipimpin oleh Ahlinya
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Tim ini berfokus pada inovasi teknologi pendidikan dan
                    pengembangan ekonomi kreatif berbasis seni kriya.
                </p>
            </div>
            <div
                class="rounded-[32px] border border-slate-200 bg-linear-to-br from-white to-slate-50 p-6 shadow-sm md:p-8"
            >
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {#if leadResearcher}
                        <article
                            class="relative flex h-full flex-col justify-between overflow-hidden rounded-[28px] border border-[#d8e7f7] bg-white p-6 shadow-[0_18px_45px_-35px_rgba(25,100,175,0.45)]"
                        >
                            <div
                                class="absolute top-0 right-0 h-28 w-28 rounded-full bg-[#eaf3fc] blur-3xl"
                            ></div>
                            <div class="relative flex flex-col items-center text-center gap-5">
                                <div
                                    class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-[24px] border border-slate-200 bg-slate-100 md:size-28"
                                >
                                    {#if leadResearcher.photo}
                                        <img
                                            src={leadResearcher.photo}
                                            alt={leadResearcher.photo_alt}
                                            class="h-full w-full object-cover"
                                        />
                                    {:else}
                                        <div
                                            class="flex h-full w-full flex-col items-center justify-center bg-linear-to-br from-[#f3f8fe] to-[#e7f1fb] text-[#1964af]"
                                        >
                                            <div
                                                class="flex size-14 items-center justify-center rounded-full bg-white text-lg font-bold shadow-sm"
                                            >
                                                {getInitials(leadResearcher.name)}
                                            </div>
                                            <p
                                                class="mt-2 text-[11px] font-medium tracking-wide text-[#4d7dad]"
                                            >
                                                Foto Segera Hadir
                                            </p>
                                        </div>
                                    {/if}
                                </div>
                                <div class="flex min-w-0 flex-col items-center">
                                    <p
                                        class="inline-flex rounded-full bg-[#eaf3fc] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#1964af]"
                                    >
                                        {leadResearcher.role}
                                    </p>
                                    <h3 class="mt-4 text-xl font-bold text-slate-900">
                                        {leadResearcher.name}
                                    </h3>
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                        {leadResearcher.department}<br />
                                        {leadResearcher.institution}
                                    </p>
                                </div>
                            </div>
                            <div class="relative mt-5 w-full border-t border-slate-100 pt-5 text-center">
                                <p class="text-sm leading-relaxed text-slate-600">
                                    {leadResearcher.expertise}
                                </p>
                            </div>
                        </article>
                    {/if}

                    {#each supportingResearchers as member (member.name)}
                        <article
                            class="flex h-full flex-col justify-between rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_-40px_rgba(15,23,42,0.4)]"
                        >
                                    <div class="flex flex-col items-center text-center gap-5">
                                        <div
                                            class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-[24px] border border-slate-200 bg-slate-100 md:size-28"
                                        >
                                    {#if member.photo}
                                        <img
                                            src={member.photo}
                                            alt={member.photo_alt}
                                            class="h-full w-full object-cover"
                                        />
                                    {:else}
                                        <div
                                            class="flex h-full w-full flex-col items-center justify-center bg-linear-to-br from-[#f8fbff] to-[#eef5fc] text-[#1964af]"
                                        >
                                            <div
                                                class="flex size-14 items-center justify-center rounded-full bg-white text-lg font-bold shadow-sm"
                                            >
                                                {getInitials(member.name)}
                                            </div>
                                            <p
                                                class="mt-2 text-[11px] font-medium tracking-wide text-[#6c89a7]"
                                            >
                                                Foto Menyusul
                                            </p>
                                        </div>
                                    {/if}
                                </div>
                                        <div class="flex min-w-0 flex-col items-center">
                                            <p
                                                class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#1964af]"
                                            >
                                                {member.role}
                                            </p>
                                            <h3 class="mt-4 text-xl font-bold text-slate-900">
                                                {member.name}
                                            </h3>
                                            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                                {member.department}<br />
                                                {member.institution}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-5 w-full border-t border-slate-100 pt-5 text-center">
                                        <p class="text-sm leading-relaxed text-slate-600">
                                            {member.expertise}
                                        </p>
                                    </div>
                                </article>
                    {/each}
                </div>

                <div class="mt-10 border-t border-slate-200 pt-8">
                    <p
                        class="mb-6 text-xs font-semibold uppercase tracking-[0.22em] text-[#1964af]"
                    >
                        Mitra Penelitian
                    </p>
                    <div class="grid gap-6 lg:grid-cols-2">
                        {#each researchPartners as partner (partner.name)}
                            <article
                                class="flex items-center gap-5 rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_-40px_rgba(15,23,42,0.4)] transition-shadow hover:shadow-md"
                            >
                                <div
                                    class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-[20px] border border-slate-200 bg-slate-100"
                                >
                                    {#if partner.photo}
                                        <img
                                            src={partner.photo}
                                            alt={partner.photo_alt}
                                            class="h-full w-full object-cover"
                                        />
                                    {:else}
                                        <div
                                            class="flex h-full w-full flex-col items-center justify-center bg-linear-to-br from-[#f8fbff] to-[#eef5fc] text-[#1964af]"
                                        >
                                            <div
                                                class="flex size-11 items-center justify-center rounded-full bg-white text-sm font-bold shadow-sm"
                                            >
                                                {getInitials(partner.name)}
                                            </div>
                                            <p
                                                class="mt-1.5 text-[10px] font-medium tracking-wide text-[#6c89a7]"
                                            >
                                                Foto Menyusul
                                            </p>
                                        </div>
                                    {/if}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-lg font-bold text-slate-900">
                                        {partner.name}
                                    </h3>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                        {partner.title}
                                    </p>
                                </div>
                            </article>
                        {/each}
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-100 pt-6 text-xs text-slate-400">
                        Dokumen disusun berdasarkan rencana penelitian
                        pengembangan BITER.
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
                <a href="/biter/kontak">
                    <Button
                        size="lg"
                        class="h-12 rounded-full bg-[#1964af] px-8 text-base font-semibold text-white hover:bg-[#1964af]"
                    >
                        Hubungi Kami
                    </Button>
                </a>
            </div>
        </div>
    </section>

    <!-- Spacer for mobile bottom nav -->
    <div class="h-16 md:hidden"></div>

    <BiterFooter />
</div>
