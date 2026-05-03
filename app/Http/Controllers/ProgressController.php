<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;

class ProgressController extends Controller
{
    public function markComplete(string $slug): RedirectResponse
    {
        $user = auth()->user();
        $lesson = Lesson::where('slug', $slug)->firstOrFail();

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now()],
        );

        return back()->with('success', 'Lesson completed!');
    }

    public function markIncomplete(string $slug): RedirectResponse
    {
        $user = auth()->user();
        $lesson = Lesson::where('slug', $slug)->firstOrFail();

        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->update(['completed_at' => null]);

        return back();
    }
}
