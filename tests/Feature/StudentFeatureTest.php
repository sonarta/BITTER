<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\User;

// --- Course Catalog ---

test('course index shows only published courses', function () {
    $instructor = User::factory()->instructor()->create();

    Course::factory()->published()->for($instructor, 'instructor')->create(['title' => 'Published']);
    Course::factory()->for($instructor, 'instructor')->create(['title' => 'Draft', 'status' => 'draft']);

    $this->get('/courses')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Courses/Index')
            ->has('courses', 1)
        );
});

test('course show page loads with modules and lessons', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    Lesson::factory()->for($module)->count(3)->create();

    $this->get("/courses/{$course->slug}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Courses/Show')
            ->has('modules', 1)
            ->has('modules.0.lessons', 3)
        );
});

test('course show returns 404 for draft courses', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create(['status' => 'draft']);

    $this->get("/courses/{$course->slug}")
        ->assertNotFound();
});

// --- Enrollment ---

test('student can enroll in a published course', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();

    $this->actingAs($student)
        ->post("/courses/{$course->slug}/enroll")
        ->assertRedirect();

    expect($student->isEnrolledIn($course))->toBeTrue();
});

test('enrolling twice does not create duplicate', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)
        ->post("/courses/{$course->slug}/enroll")
        ->assertRedirect();

    expect(Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->count())->toBe(1);
});

test('guest cannot enroll', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();

    $this->post("/courses/{$course->slug}/enroll")
        ->assertRedirect('/login');
});

// --- Learning ---

test('enrolled student can access learn page', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)
        ->get("/learn/{$course->slug}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Learn/Show')
            ->has('modules', 1)
        );
});

// --- Progress ---

test('student can mark a lesson as complete', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    $this->actingAs($student)
        ->post("/lessons/{$lesson->id}/complete")
        ->assertRedirect();

    expect(
        LessonProgress::where('user_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->whereNotNull('completed_at')
            ->exists()
    )->toBeTrue();
});

test('student can mark a lesson as incomplete', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    LessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lesson->id,
        'completed_at' => now(),
    ]);

    $this->actingAs($student)
        ->post("/lessons/{$lesson->id}/incomplete")
        ->assertRedirect();

    expect(
        LessonProgress::where('user_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->first()
            ->completed_at
    )->toBeNull();
});

test('progress percentage calculates correctly', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    $lessons = Lesson::factory()->for($module)->count(4)->create();

    // Complete 2 of 4 lessons
    foreach ($lessons->take(2) as $lesson) {
        LessonProgress::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);
    }

    expect($student->progressForCourse($course))->toBe(50);
});

// --- Dashboard ---

test('dashboard loads with real data', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats', 4)
            ->has('continue_learning')
            ->has('recommended')
            ->has('activity')
            ->has('streak')
        );
});

// --- My Learning ---

test('my learning shows enrolled courses', function () {
    $student = User::factory()->create();
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)
        ->get('/my-learning')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('MyLearning/Index')
            ->has('enrolled', 1)
        );
});
