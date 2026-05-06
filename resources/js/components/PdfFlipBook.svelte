<script lang="ts">
    import AlertCircle from 'lucide-svelte/icons/alert-circle';
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import Loader2 from 'lucide-svelte/icons/loader-2';
    import { onDestroy, onMount } from 'svelte';

    let { url }: { url: string } = $props();

    let pages: string[] = $state([]);
    let currentPage = $state(0);
    let loading = $state(true);
    let error = $state<string | null>(null);
    let animating = $state(false);
    let direction = $state<'next' | 'prev' | null>(null);
    let blobUrl = $state<string | null>(null);

    const isSpread = $derived(pages.length > 1);
    const leftPage = $derived(currentPage > 0 ? pages[currentPage - 1] : null);
    const rightPage = $derived(currentPage < pages.length ? pages[currentPage] : null);
    const totalPages = $derived(pages.length);

    function extractGoogleDriveId(input: string): string | null {
        const fileMatch = input.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
        if (fileMatch?.[1]) {
            return fileMatch[1];
        }

        const idParamMatch = input.match(/[?&]id=([a-zA-Z0-9_-]+)/);
        if (idParamMatch?.[1]) {
            return idParamMatch[1];
        }

        const ucMatch = input.match(/drive\.google\.com\/uc\?id=([a-zA-Z0-9_-]+)/);
        if (ucMatch?.[1]) {
            return ucMatch[1];
        }

        return null;
    }

    onMount(() => {
        loadPdf();
    });

    onDestroy(() => {
        if (blobUrl) {
            URL.revokeObjectURL(blobUrl);
        }
    });

    async function loadPdf() {
        loading = true;
        error = null;
        pages = [];
        currentPage = 0;

        if (blobUrl) {
            URL.revokeObjectURL(blobUrl);
            blobUrl = null;
        }

        const googleDriveId = extractGoogleDriveId(url);

        if (googleDriveId) {
            // Use Google Drive preview embed for Google Drive files
            loading = false;

            return;
        }

        try {
            // Use /storage/ directly - storage:link symlink works fine
            const pdfUrl = url;

            // Fetch PDF as ArrayBuffer to bypass browser ad blockers / tracking
            // prevention that may flag direct PDF URLs (ERR_BLOCKED_BY_CLIENT).
            const response = await fetch(pdfUrl, {
                cache: 'reload',
                credentials: 'same-origin',
                headers: { 'Cache-Control': 'no-cache', Pragma: 'no-cache' },
            });

            if (!response.ok || response.status === 204) {
                throw new Error(`Failed to fetch PDF (status ${response.status})`);
            }

            const pdfData = await response.arrayBuffer();

            if (pdfData.byteLength === 0) {
                throw new Error('PDF response was empty (0 bytes). Server may be returning 204 or response was intercepted.');
            }

            blobUrl = URL.createObjectURL(
                new Blob([pdfData], { type: 'application/pdf' }),
            );

            const pdfjsLib = await import('pdfjs-dist');

            pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
                'pdfjs-dist/build/pdf.worker.mjs',
                import.meta.url,
            ).toString();

            const loadingTask = pdfjsLib.getDocument({ data: pdfData });

            const pdf = await loadingTask.promise;
            const rendered: string[] = [];

            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const scale = 1.5;
                const viewport = page.getViewport({ scale });
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d')!;

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                await page.render({
                    canvas,
                    canvasContext: ctx,
                    viewport,
                }).promise;

                rendered.push(canvas.toDataURL('image/png'));
                page.cleanup();
            }

            pages = rendered;
            currentPage = 0;
        } catch (err) {
            error =
                err instanceof Error
                    ? err.message
                    : 'Failed to load PDF. Please check if the URL is correct and accessible.';
        } finally {
            loading = false;
        }
    }

    const googleDriveId = $derived(extractGoogleDriveId(url));

    function goNext() {
        if (currentPage + 1 >= totalPages || animating) {
            return;
        }

        animating = true;
        direction = 'next';

        setTimeout(() => {
            currentPage += 1;
            animating = false;
            direction = null;
        }, 400);
    }

    function goPrev() {
        if (currentPage <= 0 || animating) {
            return;
        }

        animating = true;
        direction = 'prev';

        setTimeout(() => {
            currentPage -= 1;
            animating = false;
            direction = null;
        }, 400);
    }

    function handleKey(e: KeyboardEvent) {
        if (e.key === 'ArrowRight') {
            goNext();
        }

        if (e.key === 'ArrowLeft') {
            goPrev();
        }

        if (e.key === 'Escape') {
            // Let parent handle close
        }
    }
</script>

<svelte:window onkeydown={handleKey} />

<div class="flex h-full flex-col">
    {#if googleDriveId}
        <iframe
            src={`https://drive.google.com/file/d/${googleDriveId}/preview`}
            title="Google Drive PDF Preview"
            class="flex-1 w-full border-0"
            allow="autoplay"
        ></iframe>
    {:else if loading}
        <div class="flex flex-1 items-center justify-center gap-2 text-muted-foreground">
            <Loader2 class="size-5 animate-spin" />
            <span class="text-sm">Loading PDF...</span>
        </div>
    {:else if error}
        <div class="flex flex-1 flex-col items-center justify-center gap-3 text-center">
            <AlertCircle class="size-8 text-destructive" />
            <p class="max-w-sm text-sm text-muted-foreground">{error}</p>
            <a
                href={blobUrl ?? url}
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm text-primary underline"
            >
                Open PDF in new tab
            </a>
        </div>
    {:else if pages.length === 0}
        <div class="flex flex-1 items-center justify-center text-sm text-muted-foreground">
            No pages found.
        </div>
    {:else}
        <div class="flex flex-1 items-center justify-center p-4">
            <div class="relative" style="perspective: 1200px;">
                <div class="flex items-stretch gap-0 shadow-2xl">
                    <!-- Left page -->
                    <div
                        class="relative flex h-[60vh] w-[45vw] max-w-[420px] items-center justify-center overflow-hidden rounded-l-md border border-r-0 bg-white"
                        style="transform-origin: right center;"
                    >
                        {#if leftPage}
                            <img
                                src={leftPage}
                                alt="Page {currentPage}"
                                class="h-full w-full object-contain"
                                draggable={false}
                            />
                        {:else}
                            <div class="flex h-full w-full items-center justify-center bg-stone-100">
                                <span class="text-xs text-stone-400">Cover</span>
                            </div>
                        {/if}
                    </div>

                    <!-- Right page (with flip animation) -->
                    <div
                        class="relative flex h-[60vh] w-[45vw] max-w-[420px] items-center justify-center overflow-hidden rounded-r-md border border-l-0 bg-white"
                        class:animate-flip-next={direction === 'next'}
                        class:animate-flip-prev={direction === 'prev'}
                        style="transform-origin: left center;"
                    >
                        {#if rightPage}
                            <img
                                src={rightPage}
                                alt="Page {currentPage + 1}"
                                class="h-full w-full object-contain"
                                draggable={false}
                            />
                        {:else}
                            <div class="flex h-full w-full items-center justify-center bg-stone-100">
                                <span class="text-xs text-stone-400">End</span>
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Book spine shadow -->
                <div
                    class="pointer-events-none absolute inset-y-0 left-1/2 w-4 -translate-x-1/2"
                    style="background: linear-gradient(to right, rgba(0,0,0,0.15), rgba(0,0,0,0.05), rgba(0,0,0,0.15));"
                ></div>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center justify-between border-t px-4 py-3">
            <button
                type="button"
                class="flex items-center gap-1 rounded-md px-3 py-1.5 text-sm transition-colors hover:bg-accent disabled:opacity-30"
                onclick={goPrev}
                disabled={currentPage <= 0 || animating}
            >
                <ChevronLeft class="size-4" />
                Previous
            </button>

            <span class="text-sm text-muted-foreground tabular-nums">
                {#if isSpread}
                    {currentPage === 0 ? 'Cover' : currentPage}
                    –
                    {currentPage + 1 > totalPages ? 'End' : currentPage + 1}
                    of {totalPages}
                {:else}
                    {currentPage + 1} / {totalPages}
                {/if}
            </span>

            <button
                type="button"
                class="flex items-center gap-1 rounded-md px-3 py-1.5 text-sm transition-colors hover:bg-accent disabled:opacity-30"
                onclick={goNext}
                disabled={currentPage + 1 >= totalPages || animating}
            >
                Next
                <ChevronRight class="size-4" />
            </button>
        </div>
    {/if}
</div>

<style>
    @keyframes flip-next {
        0% {
            transform: perspective(1200px) rotateY(0deg);
        }
        100% {
            transform: perspective(1200px) rotateY(-180deg);
        }
    }

    @keyframes flip-prev {
        0% {
            transform: perspective(1200px) rotateY(180deg);
        }
        100% {
            transform: perspective(1200px) rotateY(0deg);
        }
    }

    :global(.animate-flip-next) {
        animation: flip-next 0.4s ease-in-out forwards;
    }

    :global(.animate-flip-prev) {
        animation: flip-prev 0.4s ease-in-out forwards;
    }
</style>
