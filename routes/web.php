<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\KriyalabController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'featured' => [
            [
                'slug' => 'laravel-from-scratch',
                'title' => 'Laravel from Scratch',
                'instructor' => 'Aditya Pratama',
                'category' => 'Backend',
                'level' => 'Beginner',
                'duration_hours' => 12,
                'rating' => 4.8,
                'students' => 8430,
                'price' => 0,
                'cover' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
            ],
            [
                'slug' => 'svelte-5-runes-deep-dive',
                'title' => 'Svelte 5 Runes Deep Dive',
                'instructor' => 'Dinda Maharani',
                'category' => 'Frontend',
                'level' => 'Intermediate',
                'duration_hours' => 8,
                'rating' => 4.9,
                'students' => 2100,
                'price' => 29,
                'cover' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=800&q=80',
            ],
            [
                'slug' => 'tailwind-mastery',
                'title' => 'Tailwind CSS Mastery',
                'instructor' => 'Rio Sanjaya',
                'category' => 'Frontend',
                'level' => 'All Levels',
                'duration_hours' => 6,
                'rating' => 4.7,
                'students' => 1700,
                'price' => 19,
                'cover' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=80',
            ],
            [
                'slug' => 'pest-testing-bootcamp',
                'title' => 'Pest Testing Bootcamp',
                'instructor' => 'Intan Permata',
                'category' => 'Testing',
                'level' => 'Intermediate',
                'duration_hours' => 5,
                'rating' => 4.9,
                'students' => 200,
                'price' => 15,
                'cover' => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=800&q=80',
            ],
        ],
        'categories' => [
            ['name' => 'Backend', 'icon' => 'Server', 'count' => 24],
            ['name' => 'Frontend', 'icon' => 'Monitor', 'count' => 31],
            ['name' => 'Full-stack', 'icon' => 'Layers', 'count' => 18],
            ['name' => 'Testing', 'icon' => 'FlaskConical', 'count' => 9],
            ['name' => 'DevOps', 'icon' => 'Cloud', 'count' => 12],
            ['name' => 'Design', 'icon' => 'Palette', 'count' => 15],
        ],
        'testimonials' => [
            [
                'name' => 'Bima Saputra',
                'role' => 'Full-stack Engineer',
                'quote' => 'The Laravel course changed the way I structure my apps. Concepts finally clicked.',
                'rating' => 5,
            ],
            [
                'name' => 'Rina Wulandari',
                'role' => 'Frontend Developer',
                'quote' => 'Svelte 5 runes used to be confusing — this platform made it make sense in a weekend.',
                'rating' => 5,
            ],
            [
                'name' => 'Citra Lestari',
                'role' => 'UI Engineer',
                'quote' => 'Great instructors, hands-on projects, and the community is incredibly supportive.',
                'rating' => 4,
            ],
        ],
        'stats' => [
            ['label' => 'Active learners', 'value' => '25,000+'],
            ['label' => 'Expert instructors', 'value' => '120+'],
            ['label' => 'Hours of content', 'value' => '4,800+'],
            ['label' => 'Avg. rating', 'value' => '4.8/5'],
        ],
    ]);
})->name('home');

Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{slug}', [CourseController::class, 'show'])->name('courses.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('my-learning', [CourseController::class, 'myLearning'])->name('my-learning');
    Route::get('learn/{course}', [CourseController::class, 'learn'])->name('learn.start');
    Route::get('learn/{course}/{lesson}', [CourseController::class, 'learn'])->name('learn.lesson');

    Route::prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/', [InstructorController::class, 'index'])->name('index');
        Route::get('courses', [InstructorController::class, 'courses'])->name('courses');
    });
});

Route::prefix('kriyalab')->name('kriyalab.')->group(function () {
    Route::get('/', [KriyalabController::class, 'welcome'])->name('welcome');
    Route::get('tentang', [KriyalabController::class, 'about'])->name('about');
    Route::get('kontak', [KriyalabController::class, 'contact'])->name('contact');
    Route::post('kontak', [KriyalabController::class, 'sendContact'])->name('contact.send');
});

require __DIR__.'/settings.php';
