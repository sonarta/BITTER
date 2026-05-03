<?php

use App\Http\Controllers\BiterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BiterController::class, 'welcome'])->name('home');

Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{slug}', [CourseController::class, 'show'])->name('courses.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('my-learning', [CourseController::class, 'myLearning'])->name('my-learning');
    Route::post('courses/{slug}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::get('learn/{courseSlug}', [CourseController::class, 'learn'])->name('learn.start');
    Route::get('learn/{courseSlug}/{lessonSlug}', [CourseController::class, 'learn'])->name('learn.lesson');
    Route::post('lessons/{slug}/complete', [ProgressController::class, 'markComplete'])->name('lessons.complete');
    Route::post('lessons/{slug}/incomplete', [ProgressController::class, 'markIncomplete'])->name('lessons.incomplete');

    Route::prefix('instructor')->middleware('instructor')->name('instructor.')->group(function () {
        Route::get('/', [InstructorController::class, 'index'])->name('index');
        Route::get('courses', [InstructorController::class, 'courses'])->name('courses');
        Route::get('courses/create', [InstructorController::class, 'create'])->name('courses.create');
        Route::post('courses', [InstructorController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}/edit', [InstructorController::class, 'edit'])->name('courses.edit');
        Route::put('courses/{course}', [InstructorController::class, 'update'])->name('courses.update');
        Route::delete('courses/{course}', [InstructorController::class, 'destroy'])->name('courses.destroy');
        Route::post('courses/{course}/publish', [InstructorController::class, 'publish'])->name('courses.publish');
        Route::post('courses/{course}/unpublish', [InstructorController::class, 'unpublish'])->name('courses.unpublish');

        // Curriculum editor
        Route::get('courses/{course}/curriculum', [InstructorController::class, 'curriculum'])->name('courses.curriculum');

        // Module CRUD
        Route::post('courses/{course}/modules', [ModuleController::class, 'store'])->name('modules.store');
        Route::put('courses/{course}/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
        Route::delete('courses/{course}/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('courses/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');

        // Lesson CRUD
        Route::post('courses/{course}/modules/{module}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::put('courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('courses/{course}/modules/{module}/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');
    });
});

Route::prefix('biter')->name('biter.')->group(function () {
    Route::get('/', [BiterController::class, 'welcome'])->name('welcome');
    Route::get('tentang', [BiterController::class, 'about'])->name('about');
    Route::get('kontak', [BiterController::class, 'contact'])->name('contact');
    Route::post('kontak', [BiterController::class, 'sendContact'])->name('contact.send');
});

require __DIR__.'/settings.php';
