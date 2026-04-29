<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Home from 'lucide-svelte/icons/home';
    import Info from 'lucide-svelte/icons/info';
    import Palette from 'lucide-svelte/icons/palette';
    import { Button } from '@/components/ui/button';
    import { currentUrlState } from '@/lib/currentUrl.svelte';

    const url = currentUrlState();
    const currentPath = $derived(url.currentUrl);

    const navItems: {
        label: string;
        href: string;
        icon: typeof Home;
    }[] = [
        { label: 'Beranda', href: '/kriyalab', icon: Home },
        { label: 'Tentang', href: '/kriyalab/tentang', icon: Info },
        { label: 'Kontak', href: '/kriyalab/kontak', icon: Info },
    ];

    function isActive(href: string): boolean {
        if (href === '/kriyalab') {
            return currentPath === '/kriyalab' || currentPath === '/kriyalab/';
        }

        return currentPath.startsWith(href);
    }
</script>

<header
    class="sticky top-0 z-40 border-b border-white/10 bg-linear-to-r from-amber-600 to-yellow-500 text-white shadow-lg shadow-amber-500/20"
>
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-4">
        <Link href="/kriyalab" class="flex items-center gap-3 outline-none">
            <div
                class="flex size-11 items-center justify-center rounded-lg bg-white/20 text-white backdrop-blur-sm"
            >
                <Palette class="size-5" />
            </div>
            <div class="leading-tight">
                <p class="text-lg font-bold tracking-tight">KRIYALAB</p>
                <p class="text-[11px] text-white/70">
                    Bisnis & Kewirausahaan Kreatif
                </p>
            </div>
        </Link>

        <!-- Desktop nav -->
        <nav class="hidden items-center gap-1 md:flex">
            {#each navItems as item (item.href)}
                <Link
                    href={item.href}
                    class={[
                        'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        isActive(item.href)
                            ? 'text-white underline decoration-white/80 underline-offset-4'
                            : 'text-white/80 hover:text-white',
                    ]}
                >
                    {item.label}
                </Link>
            {/each}
        </nav>

        <div class="hidden md:block">
            <Link href="/login">
                <Button
                    size="sm"
                    class="bg-white text-indigo-700 hover:bg-white/90"
                >
                    Masuk Ke LMS
                </Button>
            </Link>
        </div>
    </div>
</header>

<!-- Mobile bottom nav -->
<nav
    class="fixed bottom-0 left-0 right-0 z-50 border-t border-white/10 bg-linear-to-r from-amber-600 to-yellow-500 text-white shadow-lg md:hidden"
>
    <div class="mx-auto flex max-w-7xl">
        {#each navItems as item (item.href)}
            {@const Icon = item.icon}
            <Link
                href={item.href}
                class={[
                    'flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 text-center transition-colors',
                    isActive(item.href)
                        ? 'text-white'
                        : 'text-white/60 hover:text-white/90',
                ]}
            >
                <Icon class="size-5" />
                <span class="text-[11px] font-medium">{item.label}</span>
            </Link>
        {/each}
        <Link
            href="/login"
            class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 text-center text-white/60 transition-colors hover:text-white/90"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="size-5"
            >
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" x2="3" y1="12" y2="12"></line>
            </svg>
            <span class="text-[11px] font-medium">Masuk</span>
        </Link>
    </div>
</nav>
