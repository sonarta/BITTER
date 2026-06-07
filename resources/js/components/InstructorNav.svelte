<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { cn } from '@/lib/utils';

    let { active }: { active: 'dashboard' | 'courses' | 'students' | 'earnings' } = $props();

    const tabs = [
        { key: 'dashboard', label: 'Dashboard', href: '/instructor' },
        { key: 'courses', label: 'My Courses', href: '/instructor/courses' },
        { key: 'students', label: 'Students', href: '/instructor/students' },
        { key: 'earnings', label: 'Earnings', href: '/instructor/earnings' },
    ] as const;
</script>

<div class="border-b">
    <div class="mx-auto flex w-full max-w-7xl items-center gap-1 overflow-x-auto px-4">
        {#each tabs as tab (tab.key)}
            <Link
                href={tab.href}
                class={cn(
                    'relative px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors',
                    active === tab.key
                        ? 'text-foreground'
                        : 'text-muted-foreground hover:text-foreground',
                )}
            >
                {tab.label}
                {#if active === tab.key}
                    <span
                        class="absolute inset-x-0 -bottom-px h-0.5 bg-primary"
                    ></span>
                {/if}
            </Link>
        {/each}
    </div>
</div>
