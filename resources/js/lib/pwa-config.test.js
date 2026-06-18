import assert from 'node:assert/strict';
import test from 'node:test';
import { resolvePwaAppName } from './pwa-config.ts';

test('uses VITE_APP_NAME when available', () => {
    assert.equal(
        resolvePwaAppName({
            APP_NAME: 'BITER',
            VITE_APP_NAME: 'BITER PWA',
        }),
        'BITER PWA',
    );
});

test('falls back to APP_NAME when VITE_APP_NAME is missing', () => {
    assert.equal(
        resolvePwaAppName({
            APP_NAME: 'BITER',
        }),
        'BITER',
    );
});

test('falls back to Laravel when app name env is missing', () => {
    assert.equal(resolvePwaAppName({}), 'BITER');
});
