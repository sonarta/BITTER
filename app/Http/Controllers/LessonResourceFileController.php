<?php

namespace App\Http\Controllers;

use App\Models\LessonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LessonResourceFileController extends Controller
{
    public function __invoke(Request $request, LessonResource $lessonResource): BinaryFileResponse
    {
        $user = $request->user();

        $lessonResource->loadMissing('lesson.module.course');

        if (! $user?->isEnrolledIn($lessonResource->lesson->module->course)) {
            abort(403);
        }

        $filePath = $this->normalizeFilePath($lessonResource);

        if ($filePath === null) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($filePath)) {
            abort(404);
        }

        $absolutePath = $disk->path($filePath);
        $filename = $this->filenameFor($lessonResource, $filePath);

        return response()->download($absolutePath, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function normalizeFilePath(LessonResource $lessonResource): ?string
    {
        if ($lessonResource->file_path !== null && $lessonResource->file_path !== '') {
            return ltrim($lessonResource->file_path, '/');
        }

        $path = parse_url($lessonResource->url, PHP_URL_PATH);

        if (! is_string($path)) {
            $path = $lessonResource->url;
        }

        if (! is_string($path)) {
            return null;
        }

        if (! Str::startsWith($path, '/storage/')) {
            return null;
        }

        return ltrim(Str::after($path, '/storage/'), '/');
    }

    private function filenameFor(LessonResource $lessonResource, string $filePath): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $base = Str::slug($lessonResource->title);

        if ($base === '') {
            $base = 'resource';
        }

        if ($extension !== '') {
            return "{$base}.{$extension}";
        }

        return $base;
    }
}
