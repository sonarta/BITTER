import assert from 'node:assert/strict';
import test from 'node:test';
import { isInstalledPwa, shouldShowInstallButton } from './pwa-install.ts';

test('detects installed PWA from standalone display mode', () => {
    const windowLike = {
        matchMedia: (query) => ({
            matches: query === '(display-mode: standalone)',
        }),
        navigator: {},
    };

    assert.equal(isInstalledPwa(windowLike), true);
});

test('detects installed PWA from iOS navigator standalone flag', () => {
    const windowLike = {
        matchMedia: () => ({ matches: false }),
        navigator: { standalone: true },
    };

    assert.equal(isInstalledPwa(windowLike), true);
});

test('does not treat regular browser mode as installed', () => {
    const windowLike = {
        matchMedia: () => ({ matches: false }),
        navigator: {},
    };

    assert.equal(isInstalledPwa(windowLike), false);
});

test('shows install button only when app is not installed and prompt exists', () => {
    const installPrompt = {};

    assert.equal(shouldShowInstallButton(false, installPrompt), true);
    assert.equal(shouldShowInstallButton(true, installPrompt), false);
    assert.equal(shouldShowInstallButton(false, null), false);
});
