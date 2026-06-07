import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
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
            strategies: 'generateSW',
            injectRegister: false,
            registerType: 'autoUpdate',
            manifest: {
                name: 'BITTER',
                short_name: 'BITTER',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                background_color: '#ffffff',
                theme_color: '#1e63a8',
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
                swDest: 'public/sw.js',
                globPatterns: ['**/*.{js,css,html,ico,png,svg,webmanifest,woff2}'],
                navigateFallback: '/build/offline.html',
                cleanupOutdatedCaches: true,
            },
        }),
    ],
});
