<script lang="ts">
    import { Badge } from '@/components/ui/badge';
    import { cn } from '@/lib/utils';

    type CoverSource = 'manual' | 'placeholder';

    const PLACEHOLDER_COVER =
        'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPSc4MDAnIGhlaWdodD0nNDUwJyB2aWV3Qm94PScwIDAgODAwIDQ1MCc+PGRlZnM+PGxpbmVhckdyYWRpZW50IGlkPSdnJyB4MT0nMCcgeTE9JzAnIHgyPScxJyB5Mj0nMSc+PHN0b3Agb2Zmc2V0PScwJyBzdG9wLWNvbG9yPScjMGYxNzJhJy8+PHN0b3Agb2Zmc2V0PScxJyBzdG9wLWNvbG9yPScjMWQ0ZWQ4Jy8+PC9saW5lYXJHcmFkaWVudD48L2RlZnM+PHJlY3Qgd2lkdGg9JzgwMCcgaGVpZ2h0PSc0NTAnIGZpbGw9J3VybCgjZyknLz48dGV4dCB4PSc1MCUnIHk9JzUwJScgZG9taW5hbnQtYmFzZWxpbmU9J21pZGRsZScgdGV4dC1hbmNob3I9J21pZGRsZScgZm9udC1mYW1pbHk9J3N5c3RlbS11aSwgLWFwcGxlLXN5c3RlbSwgU2Vnb2UgVUksIFJvYm90bywgQXJpYWwnIGZvbnQtc2l6ZT0nNDgnIGZpbGw9JyNmZmZmZmYnIG9wYWNpdHk9JzAuOTInPk5vIENvdmVyPC90ZXh0Pjwvc3ZnPg==';

    let {
        src,
        title,
        source = 'placeholder',
        class: className = '',
        imgClass = '',
        loading = 'lazy',
        showBadge = undefined,
    }: {
        src: string;
        title: string;
        source?: CoverSource;
        class?: string;
        imgClass?: string;
        loading?: 'lazy' | 'eager';
        showBadge?: boolean;
    } = $props();

    let currentSrc = $state('');
    let currentSource = $state<CoverSource>('placeholder');

    $effect(() => {
        currentSrc = src;
        currentSource = source;
    });

    function badgeText(s: CoverSource): string {
        return s === 'manual' ? 'Manual' : 'Default';
    }

    function handleError() {
        currentSrc = PLACEHOLDER_COVER;
        currentSource = 'placeholder';
    }
</script>

<div class={cn('relative', className)}>
    <img
        src={currentSrc}
        alt={title}
        loading={loading}
        class={cn('h-full w-full object-cover', imgClass)}
        onerror={handleError}
    />

    {#if showBadge ?? currentSource !== 'manual'}
        <Badge
            variant={currentSource === 'manual' ? 'outline' : 'secondary'}
            class="absolute top-3 right-3 text-[11px] font-medium"
        >
            {badgeText(currentSource)}
        </Badge>
    {/if}
</div>
