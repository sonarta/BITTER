<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Teaching', href: '/instructor' },
            { title: 'Earnings', href: '/instructor/earnings' },
        ],
    };
</script>

<script lang="ts">
    import DollarSign from 'lucide-svelte/icons/dollar-sign';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import InstructorNav from '@/components/InstructorNav.svelte';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { cn } from '@/lib/utils';

    type StatIcon = 'Users' | 'DollarSign';

    type Stat = {
        label: string;
        value: string;
        delta: string;
        trend: 'up' | 'down' | 'neutral';
        icon: StatIcon;
    };

    type RevenuePoint = { label: string; value: number };

    let {
        stats = [],
        revenue_series = [],
    }: {
        stats: Stat[];
        revenue_series: RevenuePoint[];
    } = $props();

    const statIcons = {
        Users,
        DollarSign,
    } as const;

    const maxRevenue = $derived(
        Math.max(...revenue_series.map((p) => p.value), 1),
    );
    const totalRevenue = $derived(
        revenue_series.reduce((sum, p) => sum + p.value, 0),
    );
</script>

<AppHead title="Earnings" />

<InstructorNav active="earnings" />

<div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-8">
    <section class="space-y-2">
        <h1 class="text-3xl font-bold tracking-tight">Earnings</h1>
        <p class="text-sm text-muted-foreground">
            Revenue overview based on enrollments for your courses.
        </p>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {#each stats as stat (stat.label)}
            {@const Icon = statIcons[stat.icon]}
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">
                                {stat.label}
                            </p>
                            <p class="mt-1 text-2xl font-bold">{stat.value}</p>
                            <p
                                class={cn(
                                    'mt-1 inline-flex items-center gap-1 text-xs',
                                    stat.trend === 'up' && 'text-emerald-600 dark:text-emerald-400',
                                    stat.trend === 'down' && 'text-rose-600 dark:text-rose-400',
                                    stat.trend === 'neutral' && 'text-muted-foreground',
                                )}
                            >
                                <TrendingUp
                                    class={cn(
                                        'size-3',
                                        stat.trend === 'down' && 'rotate-180',
                                        stat.trend === 'neutral' && 'opacity-60',
                                    )}
                                />
                                <span>{stat.delta || 'No change'}</span>
                            </p>
                        </div>

                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <Icon class="size-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        {/each}
    </section>

    <Card>
        <CardHeader class="flex flex-row items-start justify-between gap-4">
            <div>
                <CardTitle>Revenue (last 7 days)</CardTitle>
                <p class="mt-1 text-xs text-muted-foreground">
                    Total: Rp {totalRevenue.toLocaleString()}
                </p>
            </div>
        </CardHeader>
        <CardContent>
            <div class="grid grid-cols-7 gap-2">
                {#each revenue_series as point (point.label)}
                    <div class="flex flex-col items-center gap-2">
                        <div class="relative h-28 w-full max-w-10 overflow-hidden rounded-md bg-muted">
                            <div
                                class="absolute bottom-0 left-0 w-full rounded-md bg-primary"
                                style={`height: ${(point.value / maxRevenue) * 100}%`}
                            ></div>
                        </div>
                        <span class="text-xs text-muted-foreground">
                            {point.label}
                        </span>
                    </div>
                {/each}
            </div>
        </CardContent>
    </Card>
</div>

