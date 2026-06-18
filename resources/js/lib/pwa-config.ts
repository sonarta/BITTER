export type PwaEnv = Record<string, string | undefined>;

export function resolvePwaAppName(env: PwaEnv): string {
    return env.VITE_APP_NAME || env.APP_NAME || 'BITER';
}
