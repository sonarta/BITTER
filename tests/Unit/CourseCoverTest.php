<?php

use App\Models\Course;

test('course cover uses placeholder when cover_url is empty', function () {
    $course = new Course(['cover_url' => null]);

    $meta = $course->coverMeta();

    expect($meta['source'])->toBe('placeholder')
        ->and($meta['url'])->toStartWith('data:image/svg+xml;base64,');
});

test('course cover uses manual url when cover_url is set', function () {
    $course = new Course(['cover_url' => 'https://example.com/cover.jpg']);

    $meta = $course->coverMeta();

    expect($meta['source'])->toBe('manual')
        ->and($meta['url'])->toBe('https://example.com/cover.jpg');
});

test('course cover treats legacy default unsplash url as placeholder', function (string $legacyUrl) {
    $course = new Course(['cover_url' => $legacyUrl]);

    $meta = $course->coverMeta();

    expect($meta['source'])->toBe('placeholder')
        ->and($meta['url'])->toStartWith('data:image/svg+xml;base64,');
})->with([
    'w800' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
    'w400' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=400&q=80',
]);
