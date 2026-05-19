<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\Module;
use App\Models\User;

test('instructor can view dashboard', function () {
    $instructor = User::factory()->instructor()->create();

    Course::factory()
        ->published()
        ->for($instructor, 'instructor')
        ->count(2)
        ->create();

    $this->actingAs($instructor)
        ->get('/instructor')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/Index')
            ->has('stats', 4)
            ->has('top_courses', 2)
        );
});

test('instructor can view courses list', function () {
    $instructor = User::factory()->instructor()->create();

    Course::factory()
        ->for($instructor, 'instructor')
        ->count(3)
        ->create();

    $this->actingAs($instructor)
        ->get('/instructor/courses')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/Courses')
            ->has('courses', 3)
        );
});

test('instructor can view create course form', function () {
    $instructor = User::factory()->instructor()->create();

    $this->actingAs($instructor)
        ->get('/instructor/courses/create')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/CourseForm')
            ->where('course', null)
            ->has('categories')
        );
});

test('instructor can store a new course', function () {
    $instructor = User::factory()->instructor()->create();

    $this->actingAs($instructor)
        ->post('/instructor/courses', [
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'category' => 'Kewirausahaan',
            'level' => 'Beginner',
            'cover_url' => 'https://example.com/cover.jpg',
            'price' => 0,
        ])
        ->assertRedirect('/instructor/courses');

    expect(Course::where('slug', 'test-course')->exists())->toBeTrue();
});

test('instructor can edit own course', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->get("/instructor/courses/{$course->id}/edit")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/CourseForm')
            ->where('course.id', $course->id)
        );
});

test('instructor can update own course', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->put("/instructor/courses/{$course->id}", [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'category' => 'Bisnis',
            'level' => 'Intermediate',
            'cover_url' => '',
            'price' => 50000,
        ])
        ->assertRedirect('/instructor/courses');

    expect($course->fresh()->title)->toBe('Updated Title');
});

test('instructor cannot store a course with duplicate normalized slug', function () {
    $instructor = User::factory()->instructor()->create();

    Course::factory()->for($instructor, 'instructor')->create([
        'title' => 'Existing Course',
        'slug' => 'test-course',
    ]);

    $this->actingAs($instructor)
        ->post('/instructor/courses', [
            'title' => 'Another Course',
            'slug' => 'Test Course',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'category' => 'Kewirausahaan',
            'level' => 'Beginner',
            'cover_url' => 'https://example.com/cover.jpg',
            'price' => 0,
        ])
        ->assertInvalid('slug');
});

test('instructor cannot edit another instructor course', function () {
    $instructor = User::factory()->instructor()->create();
    $other = User::factory()->instructor()->create();
    $course = Course::factory()->for($other, 'instructor')->create();

    $this->actingAs($instructor)
        ->get("/instructor/courses/{$course->id}/edit")
        ->assertForbidden();
});

test('instructor can publish a draft course', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create(['status' => 'draft']);

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/publish")
        ->assertRedirect();

    expect($course->fresh()->status)->toBe('published');
});

test('instructor can unpublish a published course', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/unpublish")
        ->assertRedirect();

    expect($course->fresh()->status)->toBe('draft');
});

test('instructor can delete own course', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->delete("/instructor/courses/{$course->id}")
        ->assertRedirect('/instructor/courses');

    expect(Course::find($course->id))->toBeNull();
});

test('student cannot access instructor dashboard', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->get('/instructor')
        ->assertForbidden();
});

test('student cannot access instructor courses', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->get('/instructor/courses')
        ->assertForbidden();
});

test('instructor cannot update a module that does not belong to the course route', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $otherCourse = Course::factory()->for($instructor, 'instructor')->create();
    $otherModule = Module::factory()->for($otherCourse)->create(['title' => 'Original module']);

    $this->actingAs($instructor)
        ->put("/instructor/courses/{$course->id}/modules/{$otherModule->id}", [
            'title' => 'Tampered module',
        ])
        ->assertNotFound();

    expect($otherModule->fresh()->title)->toBe('Original module');
});

test('instructor cannot update a lesson that does not belong to the module route', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    $otherModule = Module::factory()->for($course)->create();
    $otherLesson = Lesson::factory()->for($otherModule)->create(['title' => 'Original lesson']);

    $this->actingAs($instructor)
        ->put("/instructor/courses/{$course->id}/modules/{$module->id}/lessons/{$otherLesson->id}", [
            'title' => 'Tampered lesson',
            'content' => 'Updated content',
            'transcript' => null,
            'video_url' => 'https://example.com/video.mp4',
            'duration_seconds' => 300,
            'is_preview' => false,
            'resources' => [],
        ])
        ->assertNotFound();

    expect($otherLesson->fresh()->title)->toBe('Original lesson');
});

test('instructor cannot update another lesson resource through the current lesson payload', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $otherLesson = Lesson::factory()->for($module)->create();
    $otherResource = LessonResource::create([
        'lesson_id' => $otherLesson->id,
        'title' => 'Original resource',
        'url' => 'https://example.com/original.pdf',
        'type' => 'PDF',
        'sort_order' => 0,
    ]);

    $this->actingAs($instructor)
        ->put("/instructor/courses/{$course->id}/modules/{$module->id}/lessons/{$lesson->id}", [
            'title' => $lesson->title,
            'content' => $lesson->content,
            'transcript' => null,
            'video_url' => $lesson->video_url,
            'duration_seconds' => $lesson->duration_seconds,
            'is_preview' => false,
            'resources' => [
                [
                    'id' => $otherResource->id,
                    'title' => 'Tampered resource',
                    'url' => 'https://example.com/tampered.pdf',
                    'type' => 'PDF',
                ],
            ],
        ])
        ->assertNotFound();

    expect($otherResource->fresh()->title)->toBe('Original resource');
});

test('guest is redirected to login', function () {
    $this->get('/instructor')
        ->assertRedirect('/login');
});
