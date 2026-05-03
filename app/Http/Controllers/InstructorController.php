<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InstructorController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        Gate::authorize('viewAny', Course::class);

        $courses = $user->taughtCourses()
            ->withCount('enrollments')
            ->get();

        $totalStudents = $courses->sum('enrollments_count');

        return Inertia::render('Instructor/Index', [
            'stats' => [
                ['label' => 'Total students', 'value' => number_format($totalStudents), 'delta' => '', 'trend' => 'neutral', 'icon' => 'Users'],
                ['label' => 'Revenue (30d)', 'value' => 'Rp 0', 'delta' => '', 'trend' => 'neutral', 'icon' => 'DollarSign'],
                ['label' => 'Avg rating', 'value' => '—', 'delta' => '', 'trend' => 'neutral', 'icon' => 'Star'],
                ['label' => 'Published courses', 'value' => (string) $courses->where('status', 'published')->count(), 'delta' => $courses->where('status', 'draft')->count().' drafts', 'trend' => 'neutral', 'icon' => 'Library'],
            ],
            'revenue_series' => [
                ['label' => 'Mon', 'value' => 0],
                ['label' => 'Tue', 'value' => 0],
                ['label' => 'Wed', 'value' => 0],
                ['label' => 'Thu', 'value' => 0],
                ['label' => 'Fri', 'value' => 0],
                ['label' => 'Sat', 'value' => 0],
                ['label' => 'Sun', 'value' => 0],
            ],
            'recent_enrollments' => $this->recentEnrollments($user),
            'top_courses' => $courses
                ->sortByDesc('enrollments_count')
                ->take(3)
                ->map(fn (Course $course) => [
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'students' => $course->enrollments_count,
                    'rating' => 0,
                    'revenue' => 0,
                    'price' => $course->price,
                    'cover' => $course->cover_url ?? 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=400&q=80',
                ])
                ->values(),
        ]);
    }

    public function courses(): Response
    {
        $user = auth()->user();

        Gate::authorize('viewAny', Course::class);

        $courses = $user->taughtCourses()
            ->withCount('enrollments')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'status' => $course->status,
                'students' => $course->enrollments_count,
                'rating' => 0,
                'revenue' => 0,
                'price' => $course->price,
                'updated_at' => $course->updated_at->diffForHumans(),
                'cover' => $course->cover_url ?? 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=400&q=80',
            ]);

        return Inertia::render('Instructor/Courses', [
            'courses' => $courses,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Course::class);

        return Inertia::render('Instructor/CourseForm', [
            'course' => null,
            'categories' => $this->categories(),
        ]);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        Gate::authorize('create', Course::class);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);
        $data['instructor_id'] = auth()->id();

        Course::create($data);

        return redirect()->route('instructor.courses')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course): Response
    {
        Gate::authorize('update', $course);

        return Inertia::render('Instructor/CourseForm', [
            'course' => $course,
            'categories' => $this->categories(),
        ]);
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        $course->update($data);

        return redirect()->route('instructor.courses')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        Gate::authorize('delete', $course);

        $course->delete();

        return redirect()->route('instructor.courses')
            ->with('success', 'Course deleted successfully.');
    }

    public function publish(Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $course->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Course published successfully.');
    }

    public function unpublish(Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $course->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return back()->with('success', 'Course unpublished.');
    }

    public function curriculum(Course $course): Response
    {
        Gate::authorize('update', $course);

        $course->load(['modules' => fn ($q) => $q->orderBy('sort_order'), 'modules.lessons' => fn ($q) => $q->orderBy('sort_order')]);

        return Inertia::render('Instructor/Curriculum', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'status' => $course->status,
                'modules' => $course->modules->map(fn ($module) => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'sort_order' => $module->sort_order,
                    'lessons' => $module->lessons->map(fn ($lesson) => [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'duration_seconds' => $lesson->duration_seconds,
                        'is_preview' => $lesson->is_preview,
                        'video_url' => $lesson->video_url,
                        'content' => $lesson->content,
                        'sort_order' => $lesson->sort_order,
                    ]),
                ]),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function recentEnrollments(mixed $user): array
    {
        return $user->taughtCourses()
            ->with(['enrollments' => fn ($q) => $q->with('user')->latest('enrolled_at')->take(5)])
            ->get()
            ->flatMap(fn (Course $course) => $course->enrollments->map(fn ($enrollment) => [
                'name' => $enrollment->user->name,
                'course' => $course->title,
                'when' => $enrollment->enrolled_at->diffForHumans(),
            ]))
            ->sortByDesc('when')
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function categories(): array
    {
        return ['Kewirausahaan', 'Bisnis', 'Praktik', 'Desain', 'Teknologi'];
    }
}
