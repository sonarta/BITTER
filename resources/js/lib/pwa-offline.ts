export const offlinePageCacheName = 'biter-offline-pages-v1';

export const offlineEnabledPaths = [
    '/',
    '/biter',
    '/biter/tentang',
    '/biter/kontak',
] as const;

function escapeRegex(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function buildOfflinePathPatternSource(path: string): string {
    const normalizedPath = normalizeOfflinePath(path);

    if (normalizedPath === '/') {
        return '/';
    }

    return `${escapeRegex(normalizedPath)}/?`;
}

export function normalizeOfflinePath(pathname: string): string {
    if (pathname.length > 1 && pathname.endsWith('/')) {
        return pathname.slice(0, -1);
    }

    return pathname;
}

export function isOfflineEnabledPath(pathname: string): boolean {
    return offlineEnabledPathMatcher.test(pathname);
}

export const offlineEnabledPathPatterns = offlineEnabledPaths.map((path) =>
    new RegExp(`^${buildOfflinePathPatternSource(path)}$`),
);

export const offlineEnabledPathMatcher = new RegExp(
    `^(?:${offlineEnabledPaths.map((path) => buildOfflinePathPatternSource(path)).join('|')})$`,
);
