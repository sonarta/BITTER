<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class InstructorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Instructor/Index', [
            'stats' => [
                ['label' => 'Total students', 'value' => '12,430', 'delta' => '+8.2%', 'trend' => 'up', 'icon' => 'Users'],
                ['label' => 'Revenue (30d)', 'value' => '$4,280', 'delta' => '+12.4%', 'trend' => 'up', 'icon' => 'DollarSign'],
                ['label' => 'Avg rating', 'value' => '4.8', 'delta' => '+0.1', 'trend' => 'up', 'icon' => 'Star'],
                ['label' => 'Published courses', 'value' => '4', 'delta' => '2 drafts', 'trend' => 'neutral', 'icon' => 'Library'],
            ],
            'revenue_series' => [
                ['label' => 'Mon', 'value' => 420],
                ['label' => 'Tue', 'value' => 560],
                ['label' => 'Wed', 'value' => 380],
                ['label' => 'Thu', 'value' => 720],
                ['label' => 'Fri', 'value' => 890],
                ['label' => 'Sat', 'value' => 1040],
                ['label' => 'Sun', 'value' => 270],
            ],
            'recent_enrollments' => [
                ['name' => 'Rina Wulandari', 'course' => 'Laravel from Scratch', 'when' => '2 min ago'],
                ['name' => 'Bima Saputra', 'course' => 'Svelte 5 Runes Deep Dive', 'when' => '14 min ago'],
                ['name' => 'Citra Lestari', 'course' => 'Tailwind CSS Mastery', 'when' => '1 h ago'],
                ['name' => 'Dimas Prayoga', 'course' => 'Laravel from Scratch', 'when' => '3 h ago'],
                ['name' => 'Eka Permata', 'course' => 'Pest Testing Bootcamp', 'when' => 'Yesterday'],
            ],
            'top_courses' => $this->myCourses()->take(3)->values(),
        ]);
    }

    public function courses(): Response
    {
        return Inertia::render('Instructor/Courses', [
            'courses' => $this->myCourses()->values(),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function myCourses(): Collection
    {
        return collect([
            [
                'slug' => 'laravel-from-scratch',
                'title' => 'Laravel from Scratch',
                'status' => 'published',
                'students' => 8430,
                'rating' => 4.8,
                'revenue' => 0,
                'price' => 0,
                'updated_at' => '2 days ago',
                'cover' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=400&q=80',
            ],
            [
                'slug' => 'svelte-5-runes-deep-dive',
                'title' => 'Svelte 5 Runes Deep Dive',
                'status' => 'published',
                'students' => 2100,
                'rating' => 4.9,
                'revenue' => 1824,
                'price' => 29,
                'updated_at' => '5 days ago',
                'cover' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&q=80',
            ],
            [
                'slug' => 'tailwind-mastery',
                'title' => 'Tailwind CSS Mastery',
                'status' => 'published',
                'students' => 1700,
                'rating' => 4.7,
                'revenue' => 960,
                'price' => 19,
                'updated_at' => '1 week ago',
                'cover' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&q=80',
            ],
            [
                'slug' => 'pest-testing-bootcamp',
                'title' => 'Pest Testing Bootcamp',
                'status' => 'published',
                'students' => 200,
                'rating' => 4.9,
                'revenue' => 300,
                'price' => 15,
                'updated_at' => '3 weeks ago',
                'cover' => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=400&q=80',
            ],
            [
                'slug' => 'inertia-v3-workshop',
                'title' => 'Inertia.js v3 Workshop',
                'status' => 'draft',
                'students' => 0,
                'rating' => 0,
                'revenue' => 0,
                'price' => 35,
                'updated_at' => '1 day ago',
                'cover' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&q=80',
            ],
            [
                'slug' => 'fortify-deep-dive',
                'title' => 'Fortify Deep Dive',
                'status' => 'draft',
                'students' => 0,
                'rating' => 0,
                'revenue' => 0,
                'price' => 25,
                'updated_at' => '4 days ago',
                'cover' => 'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?w=400&q=80',
            ],
        ]);
    }
}
