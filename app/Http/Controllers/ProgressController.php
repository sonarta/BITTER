<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;

class ProgressController extends Controller
{
    public function markComplete(Lesson $lesson): RedirectResponse
    {
        $user = auth()->user();

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now()],
        );

        return back()->with('success', 'Lesson completed!');
    }

    public function markIncomplete(Lesson $lesson): RedirectResponse
    {
        $user = auth()->user();

        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->update(['completed_at' => null]);

        return back();
    }
}
