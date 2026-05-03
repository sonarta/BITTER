<?php

use App\Models\Course;
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

test('guest is redirected to login', function () {
    $this->get('/instructor')
        ->assertRedirect('/login');
});
