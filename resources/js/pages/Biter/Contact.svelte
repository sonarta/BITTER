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
    import BiterFooter from '@/components/Biter/BiterFooter.svelte';
    import BiterHeader from '@/components/Biter/BiterHeader.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import Spinner from '@/components/ui/spinner/Spinner.svelte';

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
            value: 'BITER – Bisnis & Entrepreneurship',
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

    function getInitials(name: string): string {
        return name
            .split(' ')
            .filter(Boolean)
            .slice(0, 2)
            .map((word) => word[0]?.toUpperCase() ?? '')
            .join('');
    }
</script>

<AppHead title="Kontak Kami - BITER" />

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
            <h1
                class="text-3xl font-bold text-slate-900 md:text-5xl md:text-white"
            >
                Kontak <span class="text-[#1964af] md:text-[#cbe4f8]">Kami</span
                >
            </h1>
            <p
                class="mx-auto mt-4 max-w-2xl text-sm text-slate-600 md:text-base md:text-white/85"
            >
                Hubungi tim peneliti untuk informasi lebih lanjut tentang
                pengembangan BITER.
            </p>
        </div>
    </section>

    <!-- Contact Info card -->
    <section class="pb-8">
        <div class="mx-auto w-full max-w-6xl px-4">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-[#1964af]/5 md:p-8"
            >
                <div class="mb-6 text-center">
                    <p
                        class="inline-flex items-center gap-2 text-lg font-bold text-[#1964af]"
                    >
                        <Mail class="size-5" />
                        Informasi Kontak
                    </p>
                    <p class="mt-2 text-sm text-slate-600">
                        Untuk pertanyaan, saran, atau peluang kolaborasi terkait
                        sistem BITER, silakan hubungi tim peneliti kami.
                    </p>
                </div>
                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                >
                    {#each contactInfo as info (info.label)}
                        <div
                            class="flex flex-col items-center rounded-xl border border-slate-200 bg-slate-50 p-5 text-center"
                        >
                            <div
                                class="flex size-11 items-center justify-center rounded-lg bg-[#e8f2fc] text-[#1964af]"
                            >
                                <info.icon class="size-5" />
                            </div>
                            <p class="mt-3 text-sm font-bold">{info.label}</p>
                            <p class="mt-1 text-xs text-slate-600">
                                {info.value}
                            </p>
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
                    Akademisi yang mengembangkan ekosistem BITER.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                {#each researchers as person (person.name)}
                    <article
                        class="flex flex-col rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_-40px_rgba(15,23,42,0.4)]"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-[22px] border border-slate-200 bg-slate-100"
                            >
                                {#if person.photo}
                                    <img
                                        src={person.photo}
                                        alt={person.photo_alt}
                                        class="h-full w-full object-cover"
                                    />
                                {:else}
                                    <div
                                        class="flex h-full w-full flex-col items-center justify-center bg-linear-to-br from-[#f8fbff] to-[#eef5fc] text-[#1964af]"
                                    >
                                        <div
                                            class="flex size-11 items-center justify-center rounded-full bg-white text-sm font-bold shadow-sm"
                                        >
                                            {getInitials(person.name)}
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
                                <h3 class="truncate text-base font-bold">
                                    {person.name}
                                </h3>
                                <p
                                    class={`mt-2 inline-flex rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] ${
                                        person.type === 'lead'
                                            ? 'bg-[#eaf3fc] text-[#1964af]'
                                            : 'bg-slate-100 text-[#1964af]'
                                    }`}
                                >
                                    {person.role}
                                </p>
                            </div>
                        </div>
                        <div
                            class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm"
                        >
                            <div class="flex items-start gap-2">
                                <Building2
                                    class="mt-0.5 size-4 shrink-0 text-[#1964af]"
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
                                    class="mt-0.5 size-4 shrink-0 text-[#1964af]"
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
                            {#if person.email}
                                <div class="flex items-start gap-2">
                                    <Mail
                                        class="mt-0.5 size-4 shrink-0 text-[#1964af]"
                                    />
                                    <div class="min-w-0">
                                        <p
                                            class="text-xs font-semibold text-slate-500"
                                        >
                                            Email
                                        </p>
                                        <a
                                            href={`mailto:${person.email}`}
                                            class="block truncate text-[#1964af] hover:underline"
                                        >
                                            {person.email}
                                        </a>
                                    </div>
                                </div>
                            {/if}
                        </div>
                        <div
                            class="mt-4 rounded-lg border-l-4 border-[#1964af] bg-[#f0f7ff] p-3"
                        >
                            <p
                                class="flex items-center gap-1 text-xs font-semibold text-[#14508f]"
                            >
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
                        class="inline-flex items-center gap-2 text-lg font-bold text-[#1964af]"
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
                                class="mx-auto flex size-11 items-center justify-center rounded-lg bg-[#e8f2fc] text-[#1964af]"
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
                        class="inline-flex items-center gap-2 text-lg font-bold text-[#1964af]"
                    >
                        <Send class="size-5" />
                        Kirim Pesan
                    </p>
                    <p class="mt-2 text-sm text-slate-600">
                        Sampaikan pertanyaan atau ide kolaborasi Anda.
                    </p>
                </div>

                <Form
                    action="/biter/kontak"
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
                            class="w-full gap-2 rounded-full bg-[#1964af] py-6 text-base font-semibold text-white hover:bg-[#1964af]"
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

    <BiterFooter />
</div>
