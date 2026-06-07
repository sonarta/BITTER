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
            includeAssets: [
                'offline.html',
                'favicon.ico',
                'favicon.svg',
                'apple-touch-icon.png',
                'biru@4x.png',
            ],
            manifest: {
                name: 'BITTER',
                short_name: 'BITTER',
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
                swDest: 'public/sw.js',
                globPatterns: ['**/*.{js,css,html,ico,png,svg,webmanifest,woff2}'],
                navigateFallback: '/offline.html',
                cleanupOutdatedCaches: true,
            },
        }),
    ],
});