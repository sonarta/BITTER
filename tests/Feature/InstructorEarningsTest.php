<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

test('earnings page is disabled by default', function () {
    $instructor = User::factory()->instructor()->create();

    $this->actingAs($instructor)
        ->get('/instructor/earnings')
        ->assertNotFound();
});

test('instructor can view earnings page when enabled', function () {
    config()->set('features.earnings', true);

    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->paid(15000)->for($instructor, 'instructor')->create();
    $student = User::factory()->create();

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'completed_at' => null,
    ]);

    $this->actingAs($instructor)
        ->get('/instructor/earnings')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/Earnings')
            ->has('stats')
            ->has('revenue_series', 7)
        );
});

test('student cannot access instructor earnings page when enabled', function () {
    config()->set('features.earnings', true);

    $student = User::factory()->create();

    $this->actingAs($student)
        ->get('/instructor/earnings')
        ->assertForbidden();
});
