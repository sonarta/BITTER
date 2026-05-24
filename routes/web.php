<?php

use App\Http\Controllers\BiterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\InstructorExamController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonResourceFileController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BiterController::class, 'welcome'])->name('home');

Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{slug}', [CourseController::class, 'show'])->name('courses.show');

Route::middleware(['auth', 'verified_enabled'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('my-learning', [CourseController::class, 'myLearning'])->name('my-learning');
    Route::post('courses/{slug}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::get('learn/{courseSlug}', [CourseController::class, 'learn'])->name('learn.start');
    Route::get('learn/{courseSlug}/{lessonSlug}', [CourseController::class, 'learn'])->name('learn.lesson');
    Route::get('courses/{slug}/exam', [ExamController::class, 'show'])->name('courses.exam.show');
    Route::post('courses/{slug}/exam/start', [ExamController::class, 'start'])->name('courses.exam.start');
    Route::get('courses/{slug}/exam/attempts/{attempt}', [ExamController::class, 'take'])->name('courses.exam.take');
    Route::post('courses/{slug}/exam/attempts/{attempt}/submit', [ExamController::class, 'submit'])->name('courses.exam.submit');
    Route::get('files/lesson-resources/{lessonResource}', LessonResourceFileController::class)
        ->name('lesson-resources.file');
    Route::post('learn/{courseSlug}/{lessonSlug}/complete', [ProgressController::class, 'markComplete'])->name('lessons.complete');
    Route::post('learn/{courseSlug}/{lessonSlug}/incomplete', [ProgressController::class, 'markIncomplete'])->name('lessons.incomplete');

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

        // Exam editor
        Route::get('courses/{course}/exam', [InstructorExamController::class, 'edit'])->name('courses.exam.edit');
        Route::post('courses/{course}/exam', [InstructorExamController::class, 'upsert'])->name('courses.exam.upsert');
        Route::post('courses/{course}/exam/questions', [InstructorExamController::class, 'storeQuestion'])->name('courses.exam.questions.store');
        Route::put('courses/{course}/exam/questions/{question}', [InstructorExamController::class, 'updateQuestion'])->name('courses.exam.questions.update');
        Route::delete('courses/{course}/exam/questions/{question}', [InstructorExamController::class, 'destroyQuestion'])->name('courses.exam.questions.destroy');
        Route::post('courses/{course}/exam/questions/reorder', [InstructorExamController::class, 'reorderQuestions'])->name('courses.exam.questions.reorder');
        Route::get('courses/{course}/exam/attempts/{attempt}', [InstructorExamController::class, 'showAttempt'])->name('courses.exam.attempts.show');
        Route::post('courses/{course}/exam/attempts/{attempt}/grade', [InstructorExamController::class, 'gradeAttempt'])->name('courses.exam.attempts.grade');

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
