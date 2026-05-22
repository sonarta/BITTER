<?php

use App\Models\Course;
use App\Models\CourseExam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\User;

test('instructor dapat mempublish exam jika question valid', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 30,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => false,
    ]);

    $question = ExamQuestion::create([
        'exam_id' => $exam->id,
        'type' => 'single',
        'prompt' => '2 + 2 = ?',
        'points' => 10,
        'sort_order' => 0,
    ]);

    ExamQuestionOption::create([
        'question_id' => $question->id,
        'text' => '3',
        'is_correct' => false,
        'sort_order' => 0,
    ]);

    ExamQuestionOption::create([
        'question_id' => $question->id,
        'text' => '4',
        'is_correct' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/exam", [
            'title' => 'Final Exam',
            'description' => '',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'pass_score' => 70,
            'is_published' => true,
        ])
        ->assertRedirect();

    expect($exam->fresh()->is_published)->toBeTrue();
});

test('instructor tidak bisa mempublish exam jika single choice tanpa jawaban benar', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 30,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => false,
    ]);

    $question = ExamQuestion::create([
        'exam_id' => $exam->id,
        'type' => 'single',
        'prompt' => '2 + 2 = ?',
        'points' => 10,
        'sort_order' => 0,
    ]);

    ExamQuestionOption::create([
        'question_id' => $question->id,
        'text' => '3',
        'is_correct' => false,
        'sort_order' => 0,
    ]);

    ExamQuestionOption::create([
        'question_id' => $question->id,
        'text' => '4',
        'is_correct' => false,
        'sort_order' => 1,
    ]);

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/exam", [
            'title' => 'Final Exam',
            'description' => '',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'pass_score' => 70,
            'is_published' => true,
        ])
        ->assertInvalid('is_published');

    expect($exam->fresh()->is_published)->toBeFalse();
});
