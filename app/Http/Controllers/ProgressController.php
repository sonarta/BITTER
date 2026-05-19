<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function markComplete(string $courseSlug, string $lessonSlug): RedirectResponse
    {
        $course = Course::where('slug', $courseSlug)
            ->where('status', 'published')
            ->with('modules.lessons')
            ->firstOrFail();

        $user = Auth::user();

        if (! $user?->isEnrolledIn($course)) {
            abort(403);
        }

        $lesson = $course->modules
            ->flatMap->lessons
            ->firstWhere('slug', $lessonSlug);

        if ($lesson === null) {
            abort(404);
        }

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now()],
        );

        return back()->with('success', 'Lesson completed!');
    }

    public function markIncomplete(string $courseSlug, string $lessonSlug): RedirectResponse
    {
        $course = Course::where('slug', $courseSlug)
            ->where('status', 'published')
            ->with('modules.lessons')
            ->firstOrFail();

        $user = Auth::user();

        if (! $user?->isEnrolledIn($course)) {
            abort(403);
        }

        $lesson = $course->modules
            ->flatMap->lessons
            ->firstWhere('slug', $lessonSlug);

        if ($lesson === null) {
            abort(404);
        }

        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->update(['completed_at' => null]);

        return back();
    }
}
