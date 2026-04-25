<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                ['label' => 'Enrolled', 'value' => 3, 'icon' => 'BookOpen'],
                ['label' => 'Hours learned', 'value' => 42, 'icon' => 'Clock'],
                ['label' => 'Completed', 'value' => 1, 'icon' => 'Trophy'],
                ['label' => 'Certificates', 'value' => 1, 'icon' => 'Award'],
            ],
            'continue_learning' => [
                [
                    'slug' => 'laravel-from-scratch',
                    'title' => 'Laravel from Scratch',
                    'module_title' => 'Core Concepts',
                    'lesson_slug' => 'data-modeling',
                    'lesson_title' => 'Data Modeling',
                    'progress' => 62,
                    'cover' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
                    'remaining' => '5h 12m left',
                ],
                [
                    'slug' => 'svelte-5-runes-deep-dive',
                    'title' => 'Svelte 5 Runes Deep Dive',
                    'module_title' => 'Core Concepts',
                    'lesson_slug' => 'derived-state-with-derived',
                    'lesson_title' => 'Derived State with $derived',
                    'progress' => 28,
                    'cover' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=800&q=80',
                    'remaining' => '6h 30m left',
                ],
            ],
            'recommended' => [
                [
                    'slug' => 'pest-testing-bootcamp',
                    'title' => 'Pest Testing Bootcamp',
                    'category' => 'Testing',
                    'level' => 'Intermediate',
                    'duration_hours' => 5,
                    'rating' => 4.9,
                    'cover' => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=800&q=80',
                ],
                [
                    'slug' => 'inertia-for-laravel-devs',
                    'title' => 'Inertia.js for Laravel Developers',
                    'category' => 'Full-stack',
                    'level' => 'Intermediate',
                    'duration_hours' => 6,
                    'rating' => 4.8,
                    'cover' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80',
                ],
                [
                    'slug' => 'production-deployments',
                    'title' => 'Production-grade Deployments',
                    'category' => 'DevOps',
                    'level' => 'Advanced',
                    'duration_hours' => 11,
                    'rating' => 4.6,
                    'cover' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&q=80',
                ],
            ],
            'activity' => [
                ['label' => 'Completed lesson', 'detail' => 'Routing & Controllers', 'when' => '2 hours ago', 'icon' => 'CheckCircle2'],
                ['label' => 'Earned certificate', 'detail' => 'Tailwind CSS Mastery', 'when' => 'Yesterday', 'icon' => 'Award'],
                ['label' => 'Started course', 'detail' => 'Svelte 5 Runes Deep Dive', 'when' => '3 days ago', 'icon' => 'PlayCircle'],
                ['label' => 'Completed lesson', 'detail' => 'Setting Up Your Environment', 'when' => '4 days ago', 'icon' => 'CheckCircle2'],
            ],
            'streak' => [
                'days' => 6,
                'goal_minutes' => 30,
                'minutes_today' => 18,
            ],
        ]);
    }
}
