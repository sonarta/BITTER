export const offlinePageCacheName = 'biter-offline-pages-v1';

export const offlineEnabledPaths = [
    '/',
    '/biter',
    '/biter/tentang',
    '/biter/kontak',
] as const;

export function normalizeOfflinePath(pathname: string): string {
    if (pathname.length > 1 && pathname.endsWith('/')) {
        return pathname.slice(0, -1);
    }

    return pathname;
}

export function isOfflineEnabledPath(pathname: string): boolean {
    const normalizedPath = normalizeOfflinePath(pathname);

    return offlineEnabledPaths.includes(
        normalizedPath as (typeof offlineEnabledPaths)[number],
    );
}
