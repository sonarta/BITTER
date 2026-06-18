import assert from 'node:assert/strict';
import test from 'node:test';
import {
    isOfflineEnabledPath,
    normalizeOfflinePath,
    offlineEnabledPaths,
} from './pwa-offline.ts';

test('lists the public BITER pages that should work offline', () => {
    assert.deepEqual(offlineEnabledPaths, [
        '/',
        '/biter',
        '/biter/tentang',
        '/biter/kontak',
    ]);
});

test('normalizes trailing slashes for offline path checks', () => {
    assert.equal(normalizeOfflinePath('/biter/'), '/biter');
    assert.equal(normalizeOfflinePath('/'), '/');
});

test('recognizes allowed offline pages only', () => {
    assert.equal(isOfflineEnabledPath('/'), true);
    assert.equal(isOfflineEnabledPath('/biter/'), true);
    assert.equal(isOfflineEnabledPath('/biter/tentang'), true);
    assert.equal(isOfflineEnabledPath('/biter/kontak/'), true);
    assert.equal(isOfflineEnabledPath('/login'), false);
    assert.equal(isOfflineEnabledPath('/courses'), false);
});
