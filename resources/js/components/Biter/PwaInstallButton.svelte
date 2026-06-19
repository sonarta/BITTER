<script lang="ts">
    import Download from 'lucide-svelte/icons/download';
    import { onMount } from 'svelte';
    import { Button } from '@/components/ui/button';
    import { isInstalledPwa, shouldShowInstallButton } from '@/lib/pwa-install';
    import { cn } from '@/lib/utils';

    type Appearance = 'mobile-nav' | 'hero';

    let {
        appearance = 'mobile-nav',
        class: className = '',
    }: {
        appearance?: Appearance;
        class?: string;
    } = $props();

    let installPrompt = $state<BeforeInstallPromptEvent | null>(null);
    let isInstalled = $state(false);

    const showInstallButton = $derived(
        shouldShowInstallButton(isInstalled, installPrompt),
    );

    async function promptInstall(): Promise<void> {
        if (!installPrompt) {
            return;
        }

        const pendingInstallPrompt = installPrompt;

        installPrompt = null;

        await pendingInstallPrompt.prompt();

        const { outcome } = await pendingInstallPrompt.userChoice;

        if (outcome === 'accepted') {
            isInstalled = true;
        }
    }

    onMount(() => {
        isInstalled = isInstalledPwa(window);

        const handleBeforeInstallPrompt = (event: Event): void => {
            if (isInstalledPwa(window)) {
                installPrompt = null;
                isInstalled = true;

                return;
            }

            const beforeInstallPromptEvent = event as BeforeInstallPromptEvent;

            beforeInstallPromptEvent.preventDefault();
            installPrompt = beforeInstallPromptEvent;
        };

        const handleAppInstalled = (): void => {
            isInstalled = true;
            installPrompt = null;
        };

        window.addEventListener(
            'beforeinstallprompt',
            handleBeforeInstallPrompt,
        );
        window.addEventListener('appinstalled', handleAppInstalled);

        return () => {
            window.removeEventListener(
                'beforeinstallprompt',
                handleBeforeInstallPrompt,
            );
            window.removeEventListener('appinstalled', handleAppInstalled);
        };
    });
</script>

{#if showInstallButton}
    {#if appearance === 'hero'}
        <Button
            size="lg"
            variant="outline"
            onclick={promptInstall}
            class={cn(
                'h-12 rounded-full border-white/40 bg-white/10 px-6 text-white hover:bg-white/20 hover:text-white',
                className,
            )}
        >
            <Download class="size-4" />
            Install
        </Button>
    {:else}
        <button
            type="button"
            onclick={promptInstall}
            class={cn(
                'flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 text-center text-slate-500 transition-colors hover:text-slate-900',
                className,
            )}
        >
            <Download class="size-5" />
            <span class="text-[11px] font-medium">Install</span>
        </button>
    {/if}
{/if}
