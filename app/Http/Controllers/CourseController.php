<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(): Response
    {
        $courses = Course::query()
            ->where('status', 'published')
            ->with(['instructor', 'modules.lessons'])
            ->withCount('enrollments')
            ->get()
            ->map(fn (Course $course) => $this->formatCourse($course));

        $categories = Course::where('status', 'published')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'levels' => ['Beginner', 'Intermediate', 'Advanced'],
        ]);
    }

    public function show(string $slug): Response
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->with(['instructor', 'modules.lessons'])
            ->withCount('enrollments')
            ->firstOrFail();

        $user = auth()->user();
        $isEnrolled = $user ? $user->isEnrolledIn($course) : false;

        $modules = $course->modules->map(fn ($module) => [
            'title' => $module->title,
            'lessons' => $module->lessons->map(fn (Lesson $lesson) => [
                'title' => $lesson->title,
                'duration' => $lesson->formattedDuration(),
                'preview' => $lesson->is_preview,
            ]),
        ]);

        $related = Course::where('status', 'published')
            ->where('id', '!=', $course->id)
            ->where('category', $course->category)
            ->with(['instructor', 'modules.lessons'])
            ->withCount('enrollments')
            ->take(3)
            ->get()
            ->map(fn (Course $c) => $this->formatCourse($c));

        return Inertia::render('Courses/Show', [
            'course' => $this->formatCourse($course),
            'modules' => $modules,
            'related' => $related,
            'is_enrolled' => $isEnrolled,
        ]);
    }

    public function enroll(string $slug): RedirectResponse
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $user = auth()->user();

        if ($user->isEnrolledIn($course)) {
            return redirect()->route('learn.start', $course->slug);
        }

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        return redirect()->route('learn.start', $course->slug)
            ->with('success', 'Successfully enrolled!');
    }

    public function learn(string $courseSlug, ?string $lessonSlug = null): Response
    {
        $course = Course::where('slug', $courseSlug)
            ->where('status', 'published')
            ->with(['instructor', 'modules.lessons'])
            ->firstOrFail();

        $user = auth()->user();

        if (! $user->isEnrolledIn($course)) {
            return Inertia::render('Courses/Show', [
                'course' => $this->formatCourse($course),
                'modules' => [],
                'related' => [],
                'is_enrolled' => false,
            ]);
        }

        $completedLessonIds = $user->lessonProgress()
            ->whereNotNull('completed_at')
            ->whereIn('lesson_id', $course->lessons()->select('lessons.id'))
            ->pluck('lesson_id')
            ->toArray();

        $modules = $course->modules->map(fn ($module) => [
            'title' => $module->title,
            'lessons' => $module->lessons->map(fn (Lesson $lesson) => [
                'slug' => $lesson->slug,
                'title' => $lesson->title,
                'duration' => $lesson->formattedDuration(),
                'preview' => $lesson->is_preview,
                'completed' => in_array($lesson->id, $completedLessonIds),
            ]),
        ]);

        $flat = collect($modules)
            ->flatMap(fn (array $module) => collect($module['lessons'])->map(fn (array $lesson) => [
                ...$lesson,
                'module_title' => $module['title'],
            ]))
            ->values();

        $lessonSlug ??= $flat->first()['slug'] ?? null;

        $index = $flat->search(fn (array $lesson) => $lesson['slug'] === $lessonSlug);

        if ($index === false) {
            abort(404, "Lesson [{$lessonSlug}] not found.");
        }

        $currentFlat = $flat[$index];

        $lessonModel = $course->lessons()->where('lessons.slug', $lessonSlug)->first();

        return Inertia::render('Learn/Show', [
            'course' => [
                'slug' => $course->slug,
                'title' => $course->title,
                'instructor' => [
                    'name' => $course->instructor->name,
                    'title' => $course->instructor->role ?? 'Instructor',
                ],
            ],
            'modules' => $modules,
            'current' => array_merge($currentFlat, [
                'description' => $lessonModel?->content ?? '',
                'video_url' => $lessonModel?->video_url ?? '',
                'transcript' => $lessonModel?->transcript ?? '',
                'resources' => $lessonModel?->resources->map(fn ($r) => [
                    'title' => $r->title,
                    'url' => $r->url,
                    'type' => $r->type,
                ])->toArray() ?? [],
            ]),
            'previous' => $index > 0 ? [
                'slug' => $flat[$index - 1]['slug'],
                'title' => $flat[$index - 1]['title'],
            ] : null,
            'next' => $index < $flat->count() - 1 ? [
                'slug' => $flat[$index + 1]['slug'],
                'title' => $flat[$index + 1]['title'],
            ] : null,
            'progress' => [
                'completed' => count(array_filter($flat->all(), fn ($l) => $l['completed'])),
                'total' => $flat->count(),
            ],
        ]);
    }

    public function myLearning(): Response
    {
        $user = auth()->user();

        $enrolled = $user->enrollments()
            ->with(['course' => fn ($q) => $q->with(['instructor', 'modules.lessons'])->withCount('enrollments')])
            ->get()
            ->map(function (Enrollment $enrollment) use ($user) {
                $course = $enrollment->course;
                $progress = $user->progressForCourse($course);

                $lastCompleted = $user->lessonProgress()
                    ->whereNotNull('completed_at')
                    ->whereIn('lesson_id', $course->lessons()->select('lessons.id'))
                    ->latest('completed_at')
                    ->with('lesson')
                    ->first();

                return [
                    ...$this->formatCourse($course),
                    'progress' => $progress,
                    'last_lesson' => $lastCompleted?->lesson?->title ?? 'Not started',
                ];
            });

        return Inertia::render('MyLearning/Index', [
            'enrolled' => $enrolled,
        ]);
    }

    /**
     * Format a Course model into the array shape expected by frontend.
     *
     * @return array<string, mixed>
     */
    private function formatCourse(Course $course): array
    {
        if (! $course->relationLoaded('modules')) {
            $course->load('modules.lessons');
        }

        $lessons = $course->modules->flatMap->lessons;
        $totalSeconds = $lessons->sum('duration_seconds');
        $durationHours = round($totalSeconds / 3600, 1);

        return [
            'slug' => $course->slug,
            'title' => $course->title,
            'tagline' => $course->tagline ?? '',
            'description' => $course->description ?? '',
            'category' => $course->category,
            'level' => $course->level,
            'duration_hours' => $durationHours,
            'lessons_count' => $course->lessons_count ?? $lessons->count(),
            'students' => $course->enrollments_count ?? $course->enrollments()->count(),
            'rating' => 0,
            'price' => $course->price,
            'cover' => $course->cover_url ?? 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
            'instructor' => [
                'name' => $course->instructor->name ?? 'Unknown',
                'title' => 'Instructor',
            ],
        ];
    }
}

