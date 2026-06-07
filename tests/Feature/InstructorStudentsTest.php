<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

test('instructor can view students page', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $student = User::factory()->create();

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'completed_at' => null,
    ]);

    $this->actingAs($instructor)
        ->get('/instructor/students')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/Students')
            ->has('enrollments', 1)
        );
});

test('student cannot access instructor students page', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->get('/instructor/students')
        ->assertForbidden();
});
