import { createInertiaApp } from '@inertiajs/svelte';
import { registerSW } from 'virtual:pwa-register';
import AppLayout from '@/layouts/AppLayout.svelte';
import AuthLayout from '@/layouts/AuthLayout.svelte';
import SettingsLayout from '@/layouts/settings/Layout.svelte';
import { initializeFlashToast } from '@/lib/flash-toast';
import { initializeTheme } from '@/lib/theme.svelte';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name.startsWith('Learn/'):
            case name.startsWith('Biter/'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();

initializeFlashToast();

if (import.meta.env.PROD) {
    registerSW({
        immediate: true,
        onOfflineReady() {
            console.info('BITTER is ready to work offline.');
        },
        onNeedRefresh() {
            console.info('A new version of BITTER is available.');
        },
        onRegisterError(error: unknown) {
            console.error('Service worker registration failed.', error);
        },
    });
}
