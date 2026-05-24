<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import Folder from 'lucide-svelte/icons/folder';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Library from 'lucide-svelte/icons/library';
    import Presentation from 'lucide-svelte/icons/presentation';
    import Search from 'lucide-svelte/icons/search';
    import AppLogo from '@/components/AppLogo.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import { Button } from '@/components/ui/button';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import {
        NavigationMenu,
        NavigationMenuItem,
        NavigationMenuList,
        navigationMenuTriggerStyle,
    } from '@/components/ui/navigation-menu';
    import {
        Tooltip,
        TooltipContent,
        TooltipProvider,
        TooltipTrigger,
    } from '@/components/ui/tooltip';
    import UserMenuContent from '@/components/UserMenuContent.svelte';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { getInitials } from '@/lib/initials';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { BreadcrumbItem, NavItem } from '@/types';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs?: BreadcrumbItem[];
    } = $props();

    const auth = $derived(page.props.auth);
    const url = currentUrlState();
    const dashboardHref = toUrl(dashboard());

    const activeItemStyles =
        'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Courses',
            href: '/courses',
            icon: Library,
        },
        {
            title: 'My Learning',
            href: '/my-learning',
            icon: GraduationCap,
        },
        {
            title: 'Teaching',
            href: '/instructor',
            icon: Presentation,
        },
    ];

    const rightNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/svelte-starter-kit',
            icon: Folder,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits#svelte',
            icon: BookOpen,
        },
    ];

    function isNavItemActive(item: NavItem): boolean {
        return toUrl(item.href) === dashboardHref
            ? url.isCurrentUrl(item.href, url.currentUrl)
            : url.isCurrentOrParentUrl(item.href, url.currentUrl);
    }
</script>

<div>
    <div class="border-b border-sidebar-border/80">
        <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
            <Link href={toUrl(dashboard())} class="flex items-center gap-x-2">
                <AppLogo />
            </Link>

            <!-- Desktop Menu -->
            <div class="hidden h-full lg:flex lg:flex-1 lg:justify-center">
                <NavigationMenu class="flex h-full items-stretch">
                    <NavigationMenuList
                        class="flex h-full items-stretch space-x-2"
                    >
                        {#each mainNavItems as item (toUrl(item.href))}
                            <NavigationMenuItem
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    class="{navigationMenuTriggerStyle()} {isNavItemActive(
                                        item,
                                    )
                                        ? activeItemStyles
                                        : ''} h-9 cursor-pointer px-4"
                                    href={toUrl(item.href)}
                                >
                                    {#if item.icon}
                                        <item.icon class="mr-2 h-4 w-4" />
                                    {/if}
                                    {item.title}
                                </Link>
                                {#if isNavItemActive(item)}
                                    <div
                                        class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                    ></div>
                                {/if}
                            </NavigationMenuItem>
                        {/each}
                    </NavigationMenuList>
                </NavigationMenu>
            </div>

            <div class="ml-auto flex items-center space-x-2">
                <div class="relative flex items-center space-x-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="group h-9 w-9 cursor-pointer"
                    >
                        <Search
                            class="size-5 opacity-80 group-hover:opacity-100"
                        />
                    </Button>

                    <div class="hidden space-x-1 lg:flex">
                        {#each rightNavItems as item (toUrl(item.href))}
                            <TooltipProvider delayDuration={0}>
                                <Tooltip>
                                    <TooltipTrigger>
                                        {#snippet child({ props })}
                                            <a
                                                href={toUrl(item.href)}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                {...props}
                                                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground h-9 w-9 group cursor-pointer"
                                            >
                                                <span class="sr-only"
                                                    >{item.title}</span
                                                >
                                                <item.icon
                                                    class="size-5 opacity-80 group-hover:opacity-100"
                                                />
                                            </a>
                                        {/snippet}
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{item.title}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        {/each}
                    </div>
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        {#snippet children(props)}
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                                onclick={props.onclick}
                                aria-expanded={props['aria-expanded']}
                                data-state={props['data-state']}
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    {#if auth.user?.avatar}
                                        <AvatarImage
                                            src={auth.user.avatar}
                                            alt={auth.user?.name}
                                        />
                                    {/if}
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {getInitials(auth.user?.name ?? '')}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        {/snippet}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        {#if auth.user}
                            <UserMenuContent user={auth.user} />
                        {/if}
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </div>

    <div
        class="fixed inset-x-0 bottom-0 z-40 border-t border-sidebar-border/80 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 lg:hidden"
    >
        <div
            class="mx-auto grid h-20 max-w-7xl grid-cols-4 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        >
            {#each mainNavItems as item (toUrl(item.href))}
                <Link
                    href={toUrl(item.href)}
                    class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-1 text-[11px] font-medium transition-colors {isNavItemActive(
                        item,
                    )
                        ? 'text-foreground'
                        : 'text-muted-foreground hover:text-foreground'}"
                >
                    {#if item.icon}
                        <item.icon
                            class="size-5 {isNavItemActive(item)
                                ? 'text-foreground'
                                : 'text-muted-foreground'}"
                        />
                    {/if}
                    <span class="truncate">{item.title}</span>
                </Link>
            {/each}
        </div>
    </div>

    {#if breadcrumbs.length > 1}
        <div class="flex w-full border-b border-sidebar-border/70">
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs {breadcrumbs} />
            </div>
        </div>
    {/if}
</div>
