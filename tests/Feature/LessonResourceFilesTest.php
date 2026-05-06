<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('enrolled user can access lesson resource file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('resources/test.pdf', '%PDF-1.4 test');

    $student = User::factory()->create(['role' => 'student']);
    $course = Course::factory()->published()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    $resource = LessonResource::create([
        'lesson_id' => $lesson->id,
        'title' => 'LKM Mahasiswa',
        'url' => '/storage/resources/test.pdf',
        'file_path' => 'resources/test.pdf',
        'type' => 'PDF',
        'sort_order' => 0,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $response = $this->actingAs($student)->get(route('lesson-resources.file', $resource));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('application/octet-stream');
    expect($response->headers->get('content-disposition'))->toContain('attachment');
});

test('non-enrolled user cannot access lesson resource file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('resources/test.pdf', '%PDF-1.4 test');

    $student = User::factory()->create(['role' => 'student']);
    $course = Course::factory()->published()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    $resource = LessonResource::create([
        'lesson_id' => $lesson->id,
        'title' => 'LKM Mahasiswa',
        'url' => '/storage/resources/test.pdf',
        'file_path' => 'resources/test.pdf',
        'type' => 'PDF',
        'sort_order' => 0,
    ]);

    $this->actingAs($student)
        ->get(route('lesson-resources.file', $resource))
        ->assertForbidden();
});

test('missing file returns not found', function () {
    Storage::fake('public');

    $student = User::factory()->create(['role' => 'student']);
    $course = Course::factory()->published()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    $resource = LessonResource::create([
        'lesson_id' => $lesson->id,
        'title' => 'LKM Mahasiswa',
        'url' => '/storage/resources/missing.pdf',
        'file_path' => 'resources/missing.pdf',
        'type' => 'PDF',
        'sort_order' => 0,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)
        ->get(route('lesson-resources.file', $resource))
        ->assertNotFound();
});
