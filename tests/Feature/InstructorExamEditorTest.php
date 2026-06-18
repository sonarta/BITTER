<?php

use App\Models\Course;
use App\Models\CourseExam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\User;

test('instructor sees legacy exam editor when v2 flag is disabled', function () {
    config()->set('features.exam_editor_v2', false);

    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->get(route('instructor.courses.exam.edit', $course))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/ExamEditor')
            ->where('ui.features.exam_editor_v2', false)
            ->where('ui.active_tab', 'overview')
        );
});

test('instructor sees exam editor v2 with readiness payload when flag is enabled', function () {
    config()->set('features.exam_editor_v2', true);

    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => 'Exam draft',
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
        ->get(route('instructor.courses.exam.edit', ['course' => $course, 'tab' => 'builder']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/ExamEditorV2')
            ->where('ui.features.exam_editor_v2', true)
            ->where('ui.active_tab', 'builder')
            ->where('readiness.summary.total_questions', 1)
            ->where('readiness.summary.can_publish', true)
            ->where('readiness.summary.blockers_count', 0)
            ->where('attempts', [])
        );
});

test('readiness returns blocker when objective question has no correct answer', function () {
    config()->set('features.exam_editor_v2', true);

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
        ->get(route('instructor.courses.exam.edit', $course))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Instructor/ExamEditorV2')
            ->where('readiness.summary.can_publish', false)
            ->where('readiness.summary.blockers_count', 1)
            ->where('readiness.blockers.0.question_id', $question->id)
        );
});
