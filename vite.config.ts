import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';
import { resolvePwaAppName } from './resources/js/lib/pwa-config';
import {
    offlineEnabledPathMatcher,
    offlineEnabledPathPatterns,
    offlinePageCacheName,
} from './resources/js/lib/pwa-offline';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const appName = resolvePwaAppName(env);

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.ts'],
                refresh: true,
            }),
            inertia(),
            tailwindcss(),
            svelte(),
            wayfinder({
                formVariants: true,
            }),
            VitePWA({
                buildBase: '/build/',
                strategies: 'generateSW',
                injectRegister: false,
                registerType: 'autoUpdate',
                devOptions: {
                    enabled: false,
                    navigateFallbackAllowlist: offlineEnabledPathPatterns,
                    type: 'module',
                },
                includeAssets: [
                    'offline.html',
                    'favicon.ico',
                    'favicon.svg',
                    'apple-touch-icon.png',
                    'biru@4x.png',
                ],
                manifest: {
                    name: appName,
                    short_name: appName,
                    description:
                        'Platform Business & Entrepreneurship Mahasiswa Pendidikan Kriya berbasis Model Pembelajaran FLIS-FREAN.',
                    lang: 'id',
                    start_url: '/',
                    id: '/',
                    scope: '/',
                    display: 'standalone',
                    orientation: 'portrait',
                    background_color: '#ffffff',
                    theme_color: '#1e63a8',
                    categories: ['education', 'productivity'],
                    icons: [
                        {
                            src: '/biru@4x.png',
                            sizes: '512x512',
                            type: 'image/png',
                            purpose: 'any maskable',
                        },
                    ],
                },
                workbox: {
                    globPatterns: [
                        '**/*.{js,css,html,ico,png,svg,webmanifest,woff2}',
                    ],
                    navigateFallback: '/offline.html',
                    navigateFallbackDenylist: offlineEnabledPathPatterns,
                    cleanupOutdatedCaches: true,
                    runtimeCaching: [
                        {
                            // generateSW stringifies route matchers into the final
                            // service worker, so runtime caching must use a serializable
                            // matcher instead of referencing external helpers.
                            urlPattern: offlineEnabledPathMatcher,
                            handler: 'NetworkFirst',
                            options: {
                                cacheName: offlinePageCacheName,
                                networkTimeoutSeconds: 3,
                                cacheableResponse: {
                                    statuses: [200],
                                },
                                expiration: {
                                    maxEntries: 8,
                                    maxAgeSeconds: 60 * 60 * 24 * 7,
                                },
                            },
                        },
                        {
                            urlPattern: ({ request, sameOrigin }) =>
                                sameOrigin &&
                                (request.destination === 'style' ||
                                    request.destination === 'script' ||
                                    request.destination === 'worker'),
                            handler: 'StaleWhileRevalidate',
                            options: {
                                cacheName: 'static-resources',
                                cacheableResponse: {
                                    statuses: [0, 200],
                                },
                                expiration: {
                                    maxEntries: 64,
                                    maxAgeSeconds: 60 * 60 * 24 * 30,
                                },
                            },
                        },
                        {
                            urlPattern: ({ request, sameOrigin }) =>
                                sameOrigin && request.destination === 'image',
                            handler: 'CacheFirst',
                            options: {
                                cacheName: 'images',
                                cacheableResponse: {
                                    statuses: [0, 200],
                                },
                                expiration: {
                                    maxEntries: 128,
                                    maxAgeSeconds: 60 * 60 * 24 * 30,
                                },
                            },
                        },
                        {
                            urlPattern: ({ request, url }) =>
                                request.destination === 'font' ||
                                url.origin === 'https://fonts.bunny.net',
                            handler: 'CacheFirst',
                            options: {
                                cacheName: 'fonts',
                                cacheableResponse: {
                                    statuses: [0, 200],
                                },
                                expiration: {
                                    maxEntries: 32,
                                    maxAgeSeconds: 60 * 60 * 24 * 365,
                                },
                            },
                        },
                    ],
                },
            }),
        ],
    };
});
