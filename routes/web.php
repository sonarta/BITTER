<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\KriyalabController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KriyalabController::class, 'welcome'])->name('home');

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
