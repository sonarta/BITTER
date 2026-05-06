<script lang="ts">
    import X from 'lucide-svelte/icons/x';
    import { Dialog, DialogContent } from '@/components/ui/dialog';
    import PdfFlipBook from './PdfFlipBook.svelte';

    let {
        open = $bindable(false),
        url = '',
    }: {
        open?: boolean;
        url?: string;
    } = $props();
</script>

<Dialog bind:open>
    <DialogContent class="flex h-[90vh] max-w-5xl flex-col overflow-hidden p-0">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h3 class="text-sm font-medium">PDF Viewer</h3>
            <button
                type="button"
                class="rounded-md p-1 transition-colors hover:bg-accent"
                onclick={() => (open = false)}
                aria-label="Close"
            >
                <X class="size-4" />
            </button>
        </div>
        <div class="flex-1 overflow-hidden">
            {#if url && open}
                <PdfFlipBook {url} />
            {/if}
        </div>
    </DialogContent>
</Dialog>
