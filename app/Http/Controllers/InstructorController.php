<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class InstructorController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        assert($user instanceof User);

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
                    'cover' => $course->coverMeta()['url'],
                    'cover_source' => $course->coverMeta()['source'],
                ])
                ->values(),
        ]);
    }

    public function courses(): Response
    {
        $user = Auth::user();
        assert($user instanceof User);

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
                'cover' => $course->coverMeta()['url'],
                'cover_source' => $course->coverMeta()['source'],
            ]);

        return Inertia::render('Instructor/Courses', [
            'courses' => $courses,
        ]);
    }

    public function students(): Response
    {
        $user = Auth::user();
        assert($user instanceof User);

        Gate::authorize('viewAny', Course::class);

        $courseIds = $user->taughtCourses()->select('id');

        $enrollments = Enrollment::query()
            ->whereIn('course_id', $courseIds)
            ->with([
                'user:id,name,email',
                'course:id,title,slug',
            ])
            ->latest('enrolled_at')
            ->get()
            ->map(fn (Enrollment $enrollment) => [
                'id' => (string) $enrollment->id,
                'student' => [
                    'name' => $enrollment->user?->name ?? '—',
                    'email' => $enrollment->user?->email ?? '—',
                ],
                'course' => [
                    'title' => $enrollment->course?->title ?? '—',
                    'slug' => $enrollment->course?->slug ?? null,
                ],
                'enrolled_at' => $enrollment->enrolled_at?->diffForHumans(),
            ]);

        return Inertia::render('Instructor/Students', [
            'enrollments' => $enrollments,
        ]);
    }

    public function earnings(): Response
    {
        abort_unless(config('features.earnings'), 404);

        $user = Auth::user();
        assert($user instanceof User);

        Gate::authorize('viewAny', Course::class);

        $courseIds = $user->taughtCourses()->select('id');

        $totalStudents = Enrollment::query()
            ->whereIn('course_id', $courseIds)
            ->count();

        $revenue30d = Enrollment::query()
            ->whereIn('course_id', $courseIds)
            ->where('enrolled_at', '>=', now()->subDays(30))
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->sum('courses.price');

        $start = now()->subDays(6)->startOfDay();

        $dailyRevenue = Enrollment::query()
            ->whereIn('course_id', $courseIds)
            ->where('enrolled_at', '>=', $start)
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->selectRaw('DATE(enrollments.enrolled_at) as date, SUM(courses.price) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $revenueSeries = collect(range(0, 6))
            ->map(function (int $offset) use ($dailyRevenue, $start): array {
                $date = (clone $start)->addDays($offset);
                $key = $date->toDateString();

                return [
                    'label' => $date->format('D'),
                    'value' => (int) ($dailyRevenue[$key] ?? 0),
                ];
            });

        return Inertia::render('Instructor/Earnings', [
            'stats' => [
                [
                    'label' => 'Total students',
                    'value' => number_format($totalStudents),
                    'delta' => '',
                    'trend' => 'neutral',
                    'icon' => 'Users',
                ],
                [
                    'label' => 'Revenue (30d)',
                    'value' => 'Rp '.number_format($revenue30d),
                    'delta' => '',
                    'trend' => 'neutral',
                    'icon' => 'DollarSign',
                ],
            ],
            'revenue_series' => $revenueSeries,
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
        $data['instructor_id'] = Auth::id();

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

        $course->load([
            'modules' => fn ($q) => $q->orderBy('sort_order'),
            'modules.lessons' => fn ($q) => $q->orderBy('sort_order'),
            'modules.lessons.resources',
        ]);

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
                        'transcript' => $lesson->transcript,
                        'resources' => $lesson->resources->map(fn ($r) => [
                            'id' => $r->id,
                            'title' => $r->title,
                            'url' => $r->url,
                            'type' => $r->type,
                        ])->toArray(),
                        'sort_order' => $lesson->sort_order,
                    ]),
                ]),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function recentEnrollments(User $user): array
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
