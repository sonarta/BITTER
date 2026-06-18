type WindowLike = {
    matchMedia?: (query: string) => { matches: boolean };
    navigator?: {
        standalone?: boolean;
    };
};

const installedDisplayModes = ['standalone', 'fullscreen', 'minimal-ui'];

export function isInstalledPwa(windowLike: WindowLike): boolean {
    const isStandaloneDisplayMode = installedDisplayModes.some((displayMode) =>
        windowLike.matchMedia?.(`(display-mode: ${displayMode})`).matches ?? false,
    );

    return isStandaloneDisplayMode || Boolean(windowLike.navigator?.standalone);
}

export function shouldShowInstallButton(
    isInstalled: boolean,
    installPrompt: BeforeInstallPromptEvent | null,
): boolean {
    return !isInstalled && installPrompt !== null;
}
