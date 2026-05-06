import type { LinkComponentBaseProps } from '@inertiajs/core';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(
    href: NonNullable<LinkComponentBaseProps['href']>,
): string {
    return typeof href === 'string' ? href : href.url;
}

export function getYouTubeEmbedUrl(url: string): string | null {
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/,
    ];

    for (const pattern of patterns) {
        const match = url.match(pattern);

        if (match) {
            return `https://www.youtube.com/embed/${match[1]}`;
        }
    }

    return null;
}

export function getGoogleDrivePdfUrl(url: string): string | null {
    const match = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);

    if (match) {
        return `https://drive.google.com/uc?export=download&id=${match[1]}`;
    }

    return null;
}
