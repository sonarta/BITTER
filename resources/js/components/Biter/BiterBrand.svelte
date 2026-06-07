<script lang="ts">
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { cn } from '@/lib/utils';

    let {
        mode = 'light',
        subtitle = 'Bisnis & Entrepreneurship',
        class: className = '',
    }: {
        mode?: 'light' | 'dark';
        subtitle?: string;
        class?: string;
    } = $props();

    const isDark = $derived(mode === 'dark');
</script>

<div class={cn('flex items-center gap-3', className)}>
    <div class="flex items-center justify-center">
        <AppLogoIcon variant={isDark ? 'white' : 'blue'} class="size-11" />
    </div>
    <div class="leading-tight">
        <p
            class={cn(
                'text-lg font-bold tracking-tight',
                isDark ? 'text-white' : 'text-slate-900',
            )}
        >
            BITER
        </p>
        <p
            class={cn(
                'text-[11px]',
                isDark ? 'text-slate-400' : 'text-slate-500',
            )}
        >
            {subtitle}
        </p>
    </div>
</div>
