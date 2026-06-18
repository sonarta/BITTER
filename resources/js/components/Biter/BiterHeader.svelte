<script lang="ts">
    import Download from 'lucide-svelte/icons/download';
    import Home from 'lucide-svelte/icons/home';
    import Info from 'lucide-svelte/icons/info';
    import LogIn from 'lucide-svelte/icons/log-in';
    import Mail from 'lucide-svelte/icons/mail';
    import { onMount } from 'svelte';
    import BiterBrand from '@/components/Biter/BiterBrand.svelte';
    import { Button } from '@/components/ui/button';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import {
        isInstalledPwa,
        shouldShowInstallButton,
    } from '@/lib/pwa-install';

    const url = currentUrlState();
    const currentPath = $derived(url.currentUrl);
    let installPrompt = $state<BeforeInstallPromptEvent | null>(null);
    let isInstalled = $state(false);

    const navItems: {
        label: string;
        href: string;
        icon: typeof Home;
    }[] = [
        { label: 'Beranda', href: '/biter', icon: Home },
        { label: 'Tentang', href: '/biter/tentang', icon: Info },
        { label: 'Kontak', href: '/biter/kontak', icon: Mail },
    ];

    function isActive(href: string): boolean {
        if (href === '/biter') {
            return currentPath === '/biter' || currentPath === '/biter/';
        }

        return currentPath.startsWith(href);
    }

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

            const beforeInstallPromptEvent =
                event as BeforeInstallPromptEvent;

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

<header
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 text-slate-900 backdrop-blur-md"
>
    <div
        class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-4"
    >
        <a href="/biter" class="flex items-center gap-3 outline-none">
            <BiterBrand />
        </a>

        <!-- Desktop nav -->
        <nav class="hidden items-center gap-1 md:flex">
            {#each navItems as item (item.href)}
                <a
                    href={item.href}
                    class={[
                        'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        isActive(item.href)
                            ? 'text-[#1964af]'
                            : 'text-slate-600 hover:text-slate-900',
                    ]}
                >
                    {item.label}
                </a>
            {/each}
        </nav>

        <div class="hidden md:block">
            <a href="/login">
                <Button
                    size="sm"
                    class="bg-[#1964af] text-white hover:bg-[#1964af]"
                >
                    Masuk Ke LMS
                </Button>
            </a>
        </div>
    </div>
</header>

<!-- Mobile bottom nav -->
<nav
    class="fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200 bg-white/95 text-slate-700 backdrop-blur-md md:hidden"
>
    <div class="mx-auto flex max-w-7xl">
        {#each navItems as item (item.href)}
            {@const Icon = item.icon}
            <a
                href={item.href}
                class={[
                    'flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 text-center transition-colors',
                    isActive(item.href)
                        ? 'text-[#1964af]'
                        : 'text-slate-500 hover:text-slate-900',
                ]}
            >
                <Icon class="size-5" />
                <span class="text-[11px] font-medium">{item.label}</span>
            </a>
        {/each}
        {#if showInstallButton}
            <button
                type="button"
                onclick={promptInstall}
                class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 text-center text-slate-500 transition-colors hover:text-slate-900"
            >
                <Download class="size-5" />
                <span class="text-[11px] font-medium">Install</span>
            </button>
        {/if}
        <a
            href="/login"
            class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 text-center text-slate-500 transition-colors hover:text-slate-900"
        >
            <LogIn class="size-5" />
            <span class="text-[11px] font-medium">Masuk</span>
        </a>
    </div>
</nav>
