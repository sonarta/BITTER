<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;

// --- Curriculum page ---

test('instructor can view curriculum editor', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    Lesson::factory()->for($module)->count(3)->create();

    $this->actingAs($instructor)
        ->get("/instructor/courses/{$course->id}/curriculum")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/Curriculum')
            ->has('course.modules', 1)
            ->has('course.modules.0.lessons', 3)
        );
});

// --- Module CRUD ---

test('instructor can add a module to own course', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/modules", [
            'title' => 'New Module',
        ])
        ->assertRedirect();

    expect($course->modules)->toHaveCount(1);
    expect($course->modules->first()->title)->toBe('New Module');
});

test('instructor can update a module', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create(['title' => 'Old Title']);

    $this->actingAs($instructor)
        ->put("/instructor/courses/{$course->id}/modules/{$module->id}", [
            'title' => 'Updated Title',
        ])
        ->assertRedirect();

    expect($module->fresh()->title)->toBe('Updated Title');
});

test('instructor can delete a module', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->delete("/instructor/courses/{$course->id}/modules/{$module->id}")
        ->assertRedirect();

    expect(Module::find($module->id))->toBeNull();
});

test('deleting a module cascades to lessons', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    Lesson::factory()->for($module)->count(3)->create();

    $this->actingAs($instructor)
        ->delete("/instructor/courses/{$course->id}/modules/{$module->id}")
        ->assertRedirect();

    expect(Lesson::where('module_id', $module->id)->count())->toBe(0);
});

// --- Lesson CRUD ---

test('instructor can add a lesson to a module', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/modules/{$module->id}/lessons", [
            'title' => 'My New Lesson',
            'content' => 'Some content',
            'video_url' => 'https://example.com/video.mp4',
            'duration_seconds' => 600,
            'is_preview' => true,
        ])
        ->assertRedirect();

    $lesson = $module->lessons->first();
    expect($lesson->title)->toBe('My New Lesson');
    expect($lesson->slug)->toBe('my-new-lesson');
    expect($lesson->is_preview)->toBeTrue();
});

test('instructor can update a lesson', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create(['title' => 'Old Lesson']);

    $this->actingAs($instructor)
        ->put("/instructor/courses/{$course->id}/modules/{$module->id}/lessons/{$lesson->id}", [
            'title' => 'Updated Lesson',
            'content' => 'Updated content',
            'video_url' => '',
            'duration_seconds' => 900,
            'is_preview' => false,
        ])
        ->assertRedirect();

    expect($lesson->fresh()->title)->toBe('Updated Lesson');
    expect($lesson->fresh()->duration_seconds)->toBe(900);
});

test('instructor can delete a lesson', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    $this->actingAs($instructor)
        ->delete("/instructor/courses/{$course->id}/modules/{$module->id}/lessons/{$lesson->id}")
        ->assertRedirect();

    expect(Lesson::find($lesson->id))->toBeNull();
});

// --- Authorization ---

test('student cannot access curriculum editor', function () {
    $student = User::factory()->create();
    $course = Course::factory()->create();

    $this->actingAs($student)
        ->get("/instructor/courses/{$course->id}/curriculum")
        ->assertForbidden();
});

test('instructor cannot modify another instructor course modules', function () {
    $instructor = User::factory()->instructor()->create();
    $other = User::factory()->instructor()->create();
    $course = Course::factory()->for($other, 'instructor')->create();

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/modules", [
            'title' => 'Hijacked Module',
        ])
        ->assertForbidden();
});

test('module sort_order auto-increments', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/modules", ['title' => 'First'])
        ->assertRedirect();

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/modules", ['title' => 'Second'])
        ->assertRedirect();

    $modules = $course->modules()->orderBy('sort_order')->get();
    expect($modules[0]->sort_order)->toBe(0);
    expect($modules[1]->sort_order)->toBe(1);
});
