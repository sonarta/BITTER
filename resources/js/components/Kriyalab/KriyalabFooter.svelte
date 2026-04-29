<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Palette from 'lucide-svelte/icons/palette';
</script>

<footer class="bg-slate-900 text-slate-300">
    <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-14 md:grid-cols-4">
        <div class="md:col-span-2">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-full bg-amber-500/20 text-amber-300"
                >
                    <Palette class="size-5" />
                </div>
                <div>
                    <p class="text-lg font-bold text-white">KRIYALAB</p>
                    <p class="text-xs text-slate-400">
                        Bisnis & Kewirausahaan Kreatif Pendidikan Kriya
                    </p>
                </div>
            </div>
            <p class="mt-4 max-w-md text-sm text-slate-400">
                Platform Inkubator Bisnis Digital Mahasiswa Pendidikan Kriya
                berbasis Model Pembelajaran FLIS-FREAN. Dikembangkan melalui
                Penelitian R&D (Model ADDIE).
            </p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-indigo-300">Tim Peneliti</h4>
            <ul class="mt-3 space-y-1.5 text-sm">
                <li>Andra Saputra, M.Pd.</li>
                <li class="text-xs text-slate-400">Ketua Tim Peneliti</li>
            </ul>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-indigo-300">Institusi</h4>
            <ul class="mt-3 space-y-1.5 text-sm">
                <li>Program Studi Pendidikan Kriya</li>
                <li class="text-xs text-slate-400">FSRD, ISI Padangpanjang</li>
            </ul>
        </div>
    </div>

    <div class="border-t border-slate-800">
        <div
            class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-3 px-4 py-5 text-xs text-slate-500 md:flex-row"
        >
            <p>
                &copy; {new Date().getFullYear()} KRIYALAB. Dikembangkan untuk keperluan
                penelitian R&D.
            </p>
            <div class="flex gap-4">
                <Link href="/kriyalab/tentang" class="hover:text-slate-300">
                    Tentang
                </Link>
                <Link href="/kriyalab/kontak" class="hover:text-slate-300">
                    Kontak
                </Link>
            </div>
        </div>
    </div>
</footer>
