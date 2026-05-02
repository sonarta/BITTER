<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import CheckCircle2 from 'lucide-svelte/icons/check-circle-2';
    import FileText from 'lucide-svelte/icons/file-text';
    import FlaskConical from 'lucide-svelte/icons/flask-conical';
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
            description: 'Platform web-learning yang valid dan efektif untuk pembelajaran kewirausahaan seni.',
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
            <p
                class="text-sm font-semibold text-[#1964af] md:text-white/70"
            >
                Tentang Kami
            </p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900 md:text-5xl md:text-white">
                Mengenal Lebih Dekat <span class="text-[#1964af] md:text-[#cbe4f8]">BITER</span>
            </h1>
            <p
                class="mx-auto mt-4 max-w-2xl text-sm text-slate-600 md:text-base md:text-white/85"
            >
                Mengenal lebih dekat tentang penelitian dan pengembangan sistem BITER.
            </p>
        </div>
    </section>

    <!-- Tujuan Penelitian -->
    <section class="py-16 md:py-20">
        <div class="mx-auto grid w-full max-w-6xl gap-10 px-4 md:grid-cols-2 md:items-center">
            <div>
                <p class="text-sm font-semibold text-[#1964af]">Tujuan Penelitian</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Mengembangkan Kompetensi Bisnis Kreatif Mahasiswa
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-slate-600 md:text-base">
                    Penelitian ini bertujuan untuk mengembangkan sistem pembelajaran kewirausahaan
                    berbasis web-learning (BITER) sebagai solusi inovatif dalam meningkatkan
                    kompetensi bisnis kreatif mahasiswa. Fokus utama adalah membekali mahasiswa
                    dengan kemampuan merancang model bisnis yang adaptif dan orisinal berbasis
                    potensi lokal.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                {#each [
                    { icon: Lightbulb, label: 'Inovatif', desc: 'Solusi pembelajaran berbasis web-learning' },
                    { icon: Target, label: 'Berorientasi Hasil', desc: 'Kompetensi bisnis terukur' },
                    { icon: Users, label: 'Kolaboratif', desc: 'Potensi lokal berbasis komunitas' },
                    { icon: GraduationCap, label: 'Akademik', desc: 'Terintegrasi dengan kurikulum kriya' },
                ] as item (item.label)}
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
                    <p class="text-sm font-semibold text-[#1964af]">Visi BITER</p>
                    <h2 class="mt-2 text-2xl font-bold md:text-3xl">
                        Artpreneur di Lingkungan Kampus Seni
                    </h2>
                    <p class="mt-4 max-w-3xl text-sm leading-relaxed text-slate-600 md:text-base">
                        Memberikan pengalaman belajar yang adaptif, mandiri, dan interaktif melalui
                        simulasi model bisnis digital, mentoring, serta ruang kolaborasi yang mendorong
                        tumbuhnya jiwa <span class="font-semibold text-[#1964af]">artpreneur</span> di
                        lingkungan kampus seni.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Metodologi R&D ADDIE -->
    <section class="py-16 md:py-20">
        <div class="mx-auto w-full max-w-6xl px-4">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <p class="text-sm font-semibold text-[#1964af]">Metodologi Penelitian</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Research & Development — Model ADDIE
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Metode R&D dengan model ADDIE yang terstruktur memastikan setiap komponen
                    platform memberikan dampak pembelajaran yang terukur.
                </p>
            </div>

            <div class="flex flex-col gap-4">
                {#each addieSteps as step, idx (step.label)}
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
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#1964af]">
                                    {step.label}
                                </p>
                                <span class="text-xs text-slate-400">—</span>
                                <p class="text-xs text-slate-500">{step.title}</p>
                            </div>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-700">
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
                <p class="text-sm font-semibold text-[#1964af]">Luaran Penelitian</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">
                    Target Luaran Strategis
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Penelitian ini ditargetkan menghasilkan berbagai luaran strategis yang berdampak
                    nyata.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                {#each outcomes as item, idx (item.title)}
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
                            <p class="mt-1 text-sm leading-relaxed text-slate-600">
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
                    <p class="mt-5 text-sm font-semibold text-[#1964af]">Dampak dan Harapan</p>
                    <h3 class="mt-1 text-xl font-bold">
                        Lulusan Siap Jadi Artpreneur
                    </h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        Melalui platform BITER, diharapkan mahasiswa Pendidikan Kriya tidak hanya
                        ahli dalam berkarya seni, tetapi juga percaya diri dan kompeten dalam
                        mengelola bisnis kreatif secara mandiri. Hal ini bertujuan untuk menciptakan
                        lulusan yang siap menjadi wirausaha muda
                        <span class="font-semibold text-[#1964af]">(artpreneur)</span> yang mampu
                        bersaing di era ekonomi kreatif.
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
                    <p class="mt-5 text-sm font-semibold text-[#cbe4f8]">Transformasi Bisnis Kreatif</p>
                    <h3 class="mt-1 text-xl font-bold">
                        Katalisator Transformasi Digital
                    </h3>
                    <p class="mt-3 text-sm leading-relaxed text-white/80">
                        BITER hadir sebagai katalisator transformasi digital dalam pendidikan,
                        menghubungkan nilai pendidikan dengan teknologi modern yang relevan dengan
                        perkembangan zaman.
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
                    Tim ini berfokus pada inovasi teknologi pendidikan dan pengembangan ekonomi
                    kreatif berbasis seni kriya.
                </p>
            </div>
            <div
                class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm md:p-12"
            >
                <div class="flex flex-col items-start gap-6 md:flex-row md:items-center">
                    <div
                        class="flex size-20 shrink-0 items-center justify-center rounded-2xl bg-[#e8f2fc] text-[#1964af]"
                    >
                        <Users class="size-10" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#1964af]">
                            Ketua Tim Peneliti
                        </p>
                        <h3 class="mt-1 text-2xl font-bold">Andra Saputra, M.Pd.</h3>
                        <p class="mt-2 text-sm text-slate-500">
                            Program Studi Pendidikan Kriya, Fakultas Seni Rupa dan Desain,<br
                            /> Institut Seni Indonesia Padangpanjang.
                        </p>
                    </div>
                </div>
                <div
                    class="mt-8 border-t border-slate-100 pt-6 text-xs text-slate-400"
                >
                    Dokumen disusun berdasarkan rencana penelitian pengembangan BITER.
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
                <Link href="/biter/kontak">
                    <Button
                        size="lg"
                        class="h-12 rounded-full bg-[#1964af] px-8 text-base font-semibold text-white hover:bg-[#1964af]"
                    >
                        Hubungi Kami
                    </Button>
                </Link>
            </div>
        </div>
    </section>

    <!-- Spacer for mobile bottom nav -->
    <div class="h-16 md:hidden"></div>

    <BiterFooter />
</div>
