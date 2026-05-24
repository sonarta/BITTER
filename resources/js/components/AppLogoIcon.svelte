<script lang="ts">
    import { cn } from '@/lib/utils';

    type LogoVariant = 'blue' | 'black' | 'white';

    const logoSources: Record<LogoVariant, string> = {
        blue: '/biru@4x.png',
        black: '/hitam@4x.png',
        white: '/putih@4x.png',
    };

    let {
        variant = 'blue',
        darkVariant = null,
        alt = 'BITER logo',
        class: className = '',
        ...rest
    }: {
        variant?: LogoVariant;
        darkVariant?: LogoVariant | null;
        alt?: string;
        class?: string;
        [key: string]: unknown;
    } = $props();
</script>

{#if darkVariant}
    <img
        src={logoSources[variant]}
        {alt}
        class={cn('object-contain dark:hidden', className)}
        {...rest}
    />
    <img
        src={logoSources[darkVariant]}
        {alt}
        class={cn('hidden object-contain dark:block', className)}
        {...rest}
    />
{:else}
    <img
        src={logoSources[variant]}
        {alt}
        class={cn('object-contain', className)}
        {...rest}
    />
{/if}
