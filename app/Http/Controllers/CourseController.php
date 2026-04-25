<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Courses/Index', [
            'courses' => $this->courses()->values(),
            'categories' => $this->categories(),
            'levels' => ['Beginner', 'Intermediate', 'Advanced'],
        ]);
    }

    public function show(string $slug): Response
    {
        $course = $this->courses()->firstWhere('slug', $slug);

        if (! $course) {
            throw new NotFoundHttpException("Course [{$slug}] not found.");
        }

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'modules' => $this->modulesFor($slug),
            'related' => $this->courses()
                ->where('slug', '!=', $slug)
                ->where('category', $course['category'])
                ->take(3)
                ->values(),
        ]);
    }

    public function learn(string $courseSlug, ?string $lessonSlug = null): Response
    {
        $course = $this->courses()->firstWhere('slug', $courseSlug);

        if (! $course) {
            throw new NotFoundHttpException("Course [{$courseSlug}] not found.");
        }

        $modules = $this->modulesWithSlugs($courseSlug);

        $flat = collect($modules)
            ->flatMap(fn (array $module) => collect($module['lessons'])->map(fn (array $lesson) => [
                ...$lesson,
                'module_title' => $module['title'],
            ]))
            ->values();

        $lessonSlug ??= $flat->first()['slug'] ?? null;

        $index = $flat->search(fn (array $lesson) => $lesson['slug'] === $lessonSlug);

        if ($index === false) {
            throw new NotFoundHttpException("Lesson [{$lessonSlug}] not found.");
        }

        $current = $flat[$index];

        return Inertia::render('Learn/Show', [
            'course' => [
                'slug' => $course['slug'],
                'title' => $course['title'],
                'instructor' => $course['instructor'],
            ],
            'modules' => $modules,
            'current' => array_merge($current, [
                'description' => 'In this lesson you will explore the key concepts and see them in action with a hands-on example. Follow along with the code and pause anytime to experiment on your own.',
                'video_url' => 'https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'resources' => [
                    ['title' => 'Starter repository', 'url' => '#', 'type' => 'Repo'],
                    ['title' => 'Lesson slides (PDF)', 'url' => '#', 'type' => 'PDF'],
                    ['title' => 'Further reading', 'url' => '#', 'type' => 'Link'],
                ],
                'transcript' => "Welcome back. In this lesson we'll take a closer look at the topic, walk through a realistic example, and wrap up with a short exercise so you can practice what you learned.\n\nLet's get started by opening the project we set up earlier and reviewing where we left off...",
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
                'completed' => $index,
                'total' => $flat->count(),
            ],
        ]);
    }

    public function myLearning(): Response
    {
        $courses = $this->courses();

        $enrolled = collect([
            ['slug' => 'laravel-from-scratch', 'progress' => 62, 'last_lesson' => 'Eloquent Relationships'],
            ['slug' => 'svelte-5-runes-deep-dive', 'progress' => 28, 'last_lesson' => 'Derived State with $derived'],
            ['slug' => 'tailwind-mastery', 'progress' => 100, 'last_lesson' => 'Completed'],
        ])->map(function (array $row) use ($courses) {
            $course = $courses->firstWhere('slug', $row['slug']);

            return $course ? array_merge($course, $row) : null;
        })->filter()->values();

        return Inertia::render('MyLearning/Index', [
            'enrolled' => $enrolled,
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function courses(): Collection
    {
        return collect([
            [
                'slug' => 'laravel-from-scratch',
                'title' => 'Laravel from Scratch',
                'tagline' => 'Build production-ready web apps with the modern Laravel 12 stack.',
                'description' => 'A hands-on tour of Laravel 12: routing, Eloquent, queues, Inertia, testing with Pest, and deployment.',
                'category' => 'Backend',
                'level' => 'Beginner',
                'duration_hours' => 14,
                'lessons_count' => 48,
                'students' => 12430,
                'rating' => 4.8,
                'price' => 0,
                'cover' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
                'instructor' => ['name' => 'Ayu Pratama', 'title' => 'Senior Laravel Engineer'],
            ],
            [
                'slug' => 'svelte-5-runes-deep-dive',
                'title' => 'Svelte 5 Runes Deep Dive',
                'tagline' => 'Master $state, $derived, $effect, and the new reactivity model.',
                'description' => 'Understand how Svelte 5 runes work under the hood and migrate real Svelte 4 apps to the rune API.',
                'category' => 'Frontend',
                'level' => 'Intermediate',
                'duration_hours' => 9,
                'lessons_count' => 32,
                'students' => 4210,
                'rating' => 4.9,
                'price' => 29,
                'cover' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=800&q=80',
                'instructor' => ['name' => 'Budi Santoso', 'title' => 'Svelte Core Contributor'],
            ],
            [
                'slug' => 'tailwind-mastery',
                'title' => 'Tailwind CSS Mastery',
                'tagline' => 'Design systems, dark mode, and Tailwind v4 workflows.',
                'description' => 'From utility-first basics to building a full design system with Tailwind v4 and shadcn-svelte.',
                'category' => 'Frontend',
                'level' => 'Beginner',
                'duration_hours' => 7,
                'lessons_count' => 24,
                'students' => 8890,
                'rating' => 4.7,
                'price' => 19,
                'cover' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=80',
                'instructor' => ['name' => 'Cita Handayani', 'title' => 'UI Engineer'],
            ],
            [
                'slug' => 'inertia-for-laravel-devs',
                'title' => 'Inertia.js for Laravel Developers',
                'tagline' => 'SPA feel without the SPA headaches.',
                'description' => 'Ship modern single-page experiences with Laravel routing, Inertia v3, and Svelte.',
                'category' => 'Full-stack',
                'level' => 'Intermediate',
                'duration_hours' => 6,
                'lessons_count' => 22,
                'students' => 3120,
                'rating' => 4.8,
                'price' => 25,
                'cover' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80',
                'instructor' => ['name' => 'Dharma Wijaya', 'title' => 'Full-stack Engineer'],
            ],
            [
                'slug' => 'pest-testing-bootcamp',
                'title' => 'Pest Testing Bootcamp',
                'tagline' => 'Write tests that actually get written.',
                'description' => 'Master Pest 4 for Laravel: feature tests, datasets, architecture tests, and browser testing.',
                'category' => 'Testing',
                'level' => 'Intermediate',
                'duration_hours' => 5,
                'lessons_count' => 18,
                'students' => 2140,
                'rating' => 4.9,
                'price' => 15,
                'cover' => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=800&q=80',
                'instructor' => ['name' => 'Elia Rahman', 'title' => 'QA Lead'],
            ],
            [
                'slug' => 'production-deployments',
                'title' => 'Production-grade Deployments',
                'tagline' => 'Zero-downtime deploys, queues, and observability.',
                'description' => 'Laravel Cloud, Forge, Docker, queue workers, Horizon, and monitoring from day one.',
                'category' => 'DevOps',
                'level' => 'Advanced',
                'duration_hours' => 11,
                'lessons_count' => 36,
                'students' => 1580,
                'rating' => 4.6,
                'price' => 49,
                'cover' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&q=80',
                'instructor' => ['name' => 'Fajar Nugraha', 'title' => 'Platform Engineer'],
            ],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function categories(): array
    {
        return ['Backend', 'Frontend', 'Full-stack', 'Testing', 'DevOps'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function modulesFor(string $slug): array
    {
        return [
            [
                'title' => 'Getting Started',
                'lessons' => [
                    ['title' => 'Welcome & Course Overview', 'duration' => '4:12', 'preview' => true],
                    ['title' => 'Setting Up Your Environment', 'duration' => '9:48', 'preview' => true],
                    ['title' => 'Project Tour', 'duration' => '6:30', 'preview' => false],
                ],
            ],
            [
                'title' => 'Core Concepts',
                'lessons' => [
                    ['title' => 'Routing & Controllers', 'duration' => '12:10', 'preview' => false],
                    ['title' => 'Data Modeling', 'duration' => '14:22', 'preview' => false],
                    ['title' => 'Forms & Validation', 'duration' => '11:05', 'preview' => false],
                    ['title' => 'Testing the Basics', 'duration' => '10:40', 'preview' => false],
                ],
            ],
            [
                'title' => 'Going to Production',
                'lessons' => [
                    ['title' => 'Queues & Jobs', 'duration' => '13:55', 'preview' => false],
                    ['title' => 'Caching Strategies', 'duration' => '9:18', 'preview' => false],
                    ['title' => 'Deploying to Laravel Cloud', 'duration' => '15:04', 'preview' => false],
                ],
            ],
        ];
    }

    /**
     * Mock: decorate each lesson with a stable slug + completion flag.
     * First two lessons marked as completed for demo progress.
     *
     * @return array<int, array<string, mixed>>
     */
    private function modulesWithSlugs(string $courseSlug): array
    {
        $lessonIndex = 0;

        return collect($this->modulesFor($courseSlug))
            ->map(function (array $module) use (&$lessonIndex) {
                $module['lessons'] = collect($module['lessons'])
                    ->map(function (array $lesson) use (&$lessonIndex) {
                        $lesson['slug'] = Str::slug($lesson['title']);
                        $lesson['completed'] = $lessonIndex < 2;
                        $lessonIndex++;

                        return $lesson;
                    })
                    ->values()
                    ->all();

                return $module;
            })
            ->values()
            ->all();
    }
}
