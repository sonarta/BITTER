<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import Building2 from 'lucide-svelte/icons/building-2';
    import Globe from 'lucide-svelte/icons/globe';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import Handshake from 'lucide-svelte/icons/handshake';
    import Lightbulb from 'lucide-svelte/icons/lightbulb';
    import Mail from 'lucide-svelte/icons/mail';
    import MapPin from 'lucide-svelte/icons/map-pin';
    import Newspaper from 'lucide-svelte/icons/newspaper';
    import Send from 'lucide-svelte/icons/send';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import KriyalabFooter from '@/components/Kriyalab/KriyalabFooter.svelte';
    import KriyalabHeader from '@/components/Kriyalab/KriyalabHeader.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import Spinner from '@/components/ui/spinner/Spinner.svelte';

    type Researcher = {
        name: string;
        role: string;
        institution: string;
        expertise: string;
        email: string;
        department: string;
    };

    let {
        researchers,
        subjects,
    }: {
        researchers: Researcher[];
        subjects: string[];
    } = $props();

    const contactInfo = [
        {
            icon: Building2,
            label: 'Institusi',
            value: 'Institut Seni Indonesia Padangpanjang',
        },
        {
            icon: GraduationCap,
            label: 'Fakultas',
            value: 'FSRD - Program Studi Pendidikan Kriya',
        },
        {
            icon: Globe,
            label: 'Sistem',
            value: 'KRIYALAB – Inkubator Bisnis Kreatif',
        },
        {
            icon: Lightbulb,
            label: 'Jenis Penelitian',
            value: 'Research & Development (R&D) – Model ADDIE',
        },
    ];

    const collabInfo = [
        {
            icon: Handshake,
            title: 'Kerja Sama',
            text: 'Terbuka untuk kolaborasi penelitian pengembangan model bisnis kreatif.',
        },
        {
            icon: GraduationCap,
            title: 'Workshop',
            text: 'Pelatihan strategi kewirausahaan berbasis potensi lokal bagi mahasiswa dan praktisi seni.',
        },
        {
            icon: Newspaper,
            title: 'Publikasi',
            text: 'Hasil pengembangan sistem dipublikasikan pada jurnal ilmiah bereputasi.',
        },
    ];
</script>

<AppHead title="Kontak Kami - KRIYALAB" />

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
            <h1 class="text-3xl font-bold md:text-5xl">Kontak Kami</h1>
            <p class="mx-auto mt-4 max-w-2xl text-sm text-white/80 md:text-base">
                Hubungi tim peneliti untuk informasi lebih lanjut tentang
                pengembangan KRIYALAB.
            </p>
        </div>
    </section>

    <!-- Contact Info card -->
    <section class="pb-8">
        <div class="mx-auto w-full max-w-6xl px-4">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-amber-500/5 md:p-8"
            >
                <div class="mb-6 text-center">
                    <p
                        class="inline-flex items-center gap-2 text-lg font-bold text-amber-600"
                    >
                        <Mail class="size-5" />
                        Informasi Kontak
                    </p>
                    <p class="mt-2 text-sm text-slate-600">
                        Untuk pertanyaan, saran, atau peluang kolaborasi terkait
                        sistem KRIYALAB, silakan hubungi tim peneliti kami.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {#each contactInfo as info (info.label)}
                        <div
                            class="flex flex-col items-center rounded-xl border border-slate-200 bg-slate-50 p-5 text-center"
                        >
                            <div
                                class="flex size-11 items-center justify-center rounded-lg bg-amber-100 text-amber-600"
                            >
                                <info.icon class="size-5" />
                            </div>
                            <p class="mt-3 text-sm font-bold">{info.label}</p>
                            <p class="mt-1 text-xs text-slate-600">{info.value}</p>
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </section>

    <!-- Researcher profile -->
    <section class="py-10">
        <div class="mx-auto w-full max-w-6xl px-4">
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold md:text-3xl">
                    Profil Tim Peneliti
                </h2>
                <p class="mt-2 text-sm text-slate-600">
                    Akademisi yang mengembangkan ekosistem KRIYALAB.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                {#each researchers as person (person.email)}
                    <article
                        class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex size-16 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-amber-500 to-orange-500 text-xl font-bold text-white"
                            >
                                {person.name
                                    .split(' ')
                                    .slice(0, 2)
                                    .map((n) => n[0])
                                    .join('')}
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-bold">
                                    {person.name}
                                </h3>
                                <p class="text-xs font-medium text-amber-600">
                                    {person.role}
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                            <div class="flex items-start gap-2">
                                <Building2
                                    class="mt-0.5 size-4 shrink-0 text-amber-500"
                                />
                                <div>
                                    <p
                                        class="text-xs font-semibold text-slate-500"
                                    >
                                        Institusi
                                    </p>
                                    <p>{person.institution}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <GraduationCap
                                    class="mt-0.5 size-4 shrink-0 text-amber-500"
                                />
                                <div>
                                    <p
                                        class="text-xs font-semibold text-slate-500"
                                    >
                                        Departemen
                                    </p>
                                    <p>{person.department}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <Mail
                                    class="mt-0.5 size-4 shrink-0 text-amber-500"
                                />
                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-semibold text-slate-500"
                                    >
                                        Email
                                    </p>
                                    <a
                                        href={`mailto:${person.email}`}
                                        class="block truncate text-amber-600 hover:underline"
                                    >
                                        {person.email}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div
                            class="mt-4 rounded-lg border-l-4 border-amber-500 bg-amber-50 p-3"
                        >
                            <p class="flex items-center gap-1 text-xs font-semibold text-amber-700">
                                <MapPin class="size-3.5" />
                                Bidang Keahlian
                            </p>
                            <p class="mt-1 text-xs text-slate-700">
                                {person.expertise}
                            </p>
                        </div>
                    </article>
                {/each}
            </div>
        </div>
    </section>

    <!-- Additional info -->
    <section class="py-10">
        <div class="mx-auto w-full max-w-6xl px-4">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8"
            >
                <div class="mb-6 text-center">
                    <p
                        class="inline-flex items-center gap-2 text-lg font-bold text-amber-600"
                    >
                        <Lightbulb class="size-5" />
                        Informasi Tambahan
                    </p>
                    <p class="mt-2 text-sm text-slate-600">
                        Tim peneliti siap berkolaborasi dalam pengembangan
                        ekosistem artpreneurship dan implementasi teknologi
                        dalam pendidikan seni.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    {#each collabInfo as item (item.title)}
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-5 text-center"
                        >
                            <div
                                class="mx-auto flex size-11 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"
                            >
                                <item.icon class="size-5" />
                            </div>
                            <p class="mt-3 text-sm font-bold">{item.title}</p>
                            <p class="mt-1 text-xs text-slate-600">
                                {item.text}
                            </p>
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </section>

    <!-- Form -->
    <section class="py-10 pb-16">
        <div class="mx-auto w-full max-w-3xl px-4">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-10"
            >
                <div class="mb-6 text-center">
                    <p
                        class="inline-flex items-center gap-2 text-lg font-bold text-amber-600"
                    >
                        <Send class="size-5" />
                        Kirim Pesan
                    </p>
                    <p class="mt-2 text-sm text-slate-600">
                        Sampaikan pertanyaan atau ide kolaborasi Anda.
                    </p>
                </div>

                <Form
                    action="/kriyalab/kontak"
                    method="post"
                    resetOnSuccess
                    class="space-y-5"
                >
                    {#snippet children({ errors, processing })}
                        <div class="space-y-2">
                            <Label for="name">Nama Lengkap</Label>
                            <Input
                                id="name"
                                name="name"
                                type="text"
                                required
                                placeholder="Nama lengkap Anda"
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                required
                                placeholder="email@contoh.com"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div class="space-y-2">
                            <Label for="institution">Institusi / Sekolah</Label>
                            <Input
                                id="institution"
                                name="institution"
                                type="text"
                                placeholder="Nama institusi (opsional)"
                            />
                            <InputError message={errors.institution} />
                        </div>

                        <div class="space-y-2">
                            <Label for="subject">Subjek</Label>
                            <select
                                id="subject"
                                name="subject"
                                required
                                class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
                            >
                                <option value="">Pilih Subjek</option>
                                {#each subjects as s (s)}
                                    <option value={s}>{s}</option>
                                {/each}
                            </select>
                            <InputError message={errors.subject} />
                        </div>

                        <div class="space-y-2">
                            <Label for="message">Pesan</Label>
                            <textarea
                                id="message"
                                name="message"
                                required
                                rows="5"
                                placeholder="Tuliskan pesan Anda di sini..."
                                class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
                            ></textarea>
                            <InputError message={errors.message} />
                        </div>

                        <Button
                            type="submit"
                            disabled={processing}
                            class="w-full gap-2 rounded-full bg-linear-to-r from-amber-500 to-orange-500 py-6 text-base font-semibold text-white hover:opacity-95"
                        >
                            {#if processing}<Spinner />{/if}
                            <Send class="size-4" />
                            Kirim Pesan
                        </Button>
                    {/snippet}
                </Form>
            </div>
        </div>
    </section>

    <!-- Spacer for mobile bottom nav -->
    <div class="h-16 md:hidden"></div>

    <KriyalabFooter />
</div>
