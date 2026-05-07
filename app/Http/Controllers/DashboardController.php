<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $enrollments = $user->enrollments()
            ->with(['course' => fn ($q) => $q->with(['instructor', 'modules.lessons'])->withCount('enrollments')])
            ->get();

        $enrolledCount = $enrollments->count();

        $completedLessonIds = $user->lessonProgress()
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        $totalCompletedSeconds = 0;

        if ($completedLessonIds->isNotEmpty()) {
            $totalCompletedSeconds = Lesson::whereIn('id', $completedLessonIds)->sum('duration_seconds');
        }

        $hoursLearned = round($totalCompletedSeconds / 3600, 1);

        $completedCourses = $enrollments->filter(function (Enrollment $enrollment) use ($user) {
            $progress = $user->progressForCourse($enrollment->course);

            return $progress === 100;
        })->count();

        $continueLearning = $enrollments
            ->filter(fn (Enrollment $e) => $user->progressForCourse($e->course) < 100)
            ->take(3)
            ->map(function (Enrollment $enrollment) use ($user) {
                $course = $enrollment->course;
                $course->load(['modules.lessons']);

                $progress = $user->progressForCourse($course);

                $completedIds = $user->lessonProgress()
                    ->whereNotNull('completed_at')
                    ->pluck('lesson_id')
                    ->toArray();

                $totalSeconds = 0;
                $completedSeconds = 0;
                $nextLesson = null;
                $nextModuleTitle = '';

                foreach ($course->modules->sortBy('sort_order') as $module) {
                    foreach ($module->lessons->sortBy('sort_order') as $lesson) {
                        $totalSeconds += $lesson->duration_seconds;
                        if (in_array($lesson->id, $completedIds)) {
                            $completedSeconds += $lesson->duration_seconds;
                        } elseif (! $nextLesson) {
                            $nextLesson = $lesson;
                            $nextModuleTitle = $module->title;
                        }
                    }
                }

                $remainingSeconds = max(0, $totalSeconds - $completedSeconds);
                $remainingHours = (int) floor($remainingSeconds / 3600);
                $remainingMinutes = (int) floor(($remainingSeconds % 3600) / 60);

                return [
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'module_title' => $nextModuleTitle,
                    'lesson_slug' => $nextLesson?->slug ?? '',
                    'lesson_title' => $nextLesson?->title ?? 'Completed',
                    'progress' => $progress,
                    'cover' => $course->coverMeta()['url'],
                    'cover_source' => $course->coverMeta()['source'],
                    'remaining' => "{$remainingHours}h {$remainingMinutes}m left",
                ];
            })
            ->values();

        $enrolledCourseIds = $enrollments->pluck('course_id');
        $recommended = Course::where('status', 'published')
            ->whereNotIn('id', $enrolledCourseIds)
            ->with(['instructor', 'modules.lessons'])
            ->withCount('enrollments')
            ->take(3)
            ->get()
            ->map(function (Course $course) {
                $totalSeconds = $course->modules->flatMap->lessons->sum('duration_seconds');

                return [
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'category' => $course->category,
                    'level' => $course->level,
                    'duration_hours' => round($totalSeconds / 3600, 1),
                    'rating' => 0,
                    'cover' => $course->coverMeta()['url'],
                    'cover_source' => $course->coverMeta()['source'],
                ];
            });

        $recentProgress = $user->lessonProgress()
            ->whereNotNull('completed_at')
            ->with('lesson')
            ->latest('completed_at')
            ->take(4)
            ->get()
            ->map(fn (LessonProgress $lp) => [
                'label' => 'Completed lesson',
                'detail' => $lp->lesson->title,
                'when' => $lp->completed_at->diffForHumans(),
                'icon' => 'CheckCircle2',
            ]);

        $recentEnrollments = $user->enrollments()
            ->latest('enrolled_at')
            ->take(2)
            ->with('course')
            ->get()
            ->map(fn (Enrollment $e) => [
                'label' => 'Started course',
                'detail' => $e->course->title,
                'when' => $e->enrolled_at->diffForHumans(),
                'icon' => 'PlayCircle',
            ]);

        $activity = collect($recentProgress)
            ->merge($recentEnrollments)
            ->sortByDesc('when')
            ->take(5)
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                ['label' => 'Enrolled', 'value' => $enrolledCount, 'icon' => 'BookOpen'],
                ['label' => 'Hours learned', 'value' => $hoursLearned, 'icon' => 'Clock'],
                ['label' => 'Completed', 'value' => $completedCourses, 'icon' => 'Trophy'],
                ['label' => 'Certificates', 'value' => $completedCourses, 'icon' => 'Award'],
            ],
            'continue_learning' => $continueLearning,
            'recommended' => $recommended,
            'activity' => $activity,
            'streak' => [
                'days' => $this->calculateStreak($user),
                'goal_minutes' => 30,
                'minutes_today' => round($this->minutesToday($user)),
            ],
        ]);
    }

    /**
     * Calculate consecutive learning days streak.
     */
    private function calculateStreak(mixed $user): int
    {
        $dates = $user->lessonProgress()
            ->whereNotNull('completed_at')
            ->selectRaw('DATE(completed_at) as day')
            ->distinct()
            ->orderByDesc('day')
            ->pluck('day')
            ->map(fn ($d) => Carbon::parse($d));

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $expected = now()->startOfDay();

        foreach ($dates as $date) {
            if ($date->equalTo($expected)) {
                $streak++;
                $expected = $expected->subDay();
            } elseif ($date->equalTo($expected->copy()->subDay())) {
                $streak++;
                $expected = $date->copy()->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calculate minutes learned today.
     */
    private function minutesToday(mixed $user): float
    {
        $todayLessonIds = $user->lessonProgress()
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', today())
            ->pluck('lesson_id');

        if ($todayLessonIds->isEmpty()) {
            return 0;
        }

        return Lesson::whereIn('id', $todayLessonIds)->sum('duration_seconds') / 60;
    }
}
