import assert from 'node:assert/strict';
import test from 'node:test';
import {
    isOfflineEnabledPath,
    offlineEnabledPathMatcher,
    normalizeOfflinePath,
    offlineEnabledPaths,
    offlineEnabledPathPatterns,
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

test('builds regex patterns for the offline-enabled pages', () => {
    assert.equal(offlineEnabledPathPatterns[0].test('/'), true);
    assert.equal(offlineEnabledPathPatterns[1].test('/biter/'), true);
    assert.equal(offlineEnabledPathPatterns[2].test('/biter/tentang'), true);
    assert.equal(offlineEnabledPathPatterns[3].test('/biter/kontak/'), true);
    assert.equal(offlineEnabledPathPatterns[3].test('/login'), false);
});

test('builds a combined matcher for service worker runtime caching', () => {
    assert.equal(offlineEnabledPathMatcher.test('/'), true);
    assert.equal(offlineEnabledPathMatcher.test('/biter'), true);
    assert.equal(offlineEnabledPathMatcher.test('/biter/tentang/'), true);
    assert.equal(offlineEnabledPathMatcher.test('/biter/kontak'), true);
    assert.equal(offlineEnabledPathMatcher.test('/login'), false);
});
