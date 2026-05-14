<?php

use App\Models\Course;
use App\Models\CourseExam;
use App\Models\Enrollment;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\User;
use Illuminate\Support\Carbon;

test('student must be enrolled to access exam', function () {
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->create();

    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => true,
    ]);

    $this->actingAs($student)
        ->get("/courses/{$course->slug}/exam")
        ->assertForbidden();
});

test('student can start exam and attempt limit is enforced', function () {
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->create();

    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => true,
    ]);
    ExamQuestion::create([
        'exam_id' => $exam->id,
        'type' => 'essay',
        'prompt' => 'Explain one concept from this course.',
        'points' => 10,
        'sort_order' => 0,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)
        ->post("/courses/{$course->slug}/exam/start")
        ->assertRedirect();

    expect(ExamAttempt::where('exam_id', $exam->id)->where('user_id', $student->id)->count())->toBe(1);

    $attempt = ExamAttempt::where('exam_id', $exam->id)->where('user_id', $student->id)->firstOrFail();

    $this->actingAs($student)
        ->from("/courses/{$course->slug}/exam")
        ->post("/courses/{$course->slug}/exam/start")
        ->assertRedirect("/courses/{$course->slug}/exam/attempts/{$attempt->id}");

    expect(ExamAttempt::where('exam_id', $exam->id)->where('user_id', $student->id)->count())->toBe(1);

    $attempt->update(['status' => 'submitted', 'submitted_at' => now()]);

    $this->actingAs($student)
        ->from("/courses/{$course->slug}/exam")
        ->post("/courses/{$course->slug}/exam/start")
        ->assertRedirect("/courses/{$course->slug}/exam")
        ->assertSessionHas('error');
});

test('objective questions are auto-graded and can pass', function () {
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->create();

    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => true,
    ]);

    $q = ExamQuestion::create([
        'exam_id' => $exam->id,
        'type' => 'single',
        'prompt' => '2 + 2 = ?',
        'points' => 10,
        'sort_order' => 0,
    ]);

    $wrong = ExamQuestionOption::create([
        'question_id' => $q->id,
        'text' => '3',
        'is_correct' => false,
        'sort_order' => 0,
    ]);

    $correct = ExamQuestionOption::create([
        'question_id' => $q->id,
        'text' => '4',
        'is_correct' => true,
        'sort_order' => 1,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)->post("/courses/{$course->slug}/exam/start");

    $attempt = ExamAttempt::where('exam_id', $exam->id)->where('user_id', $student->id)->firstOrFail();

    $this->actingAs($student)
        ->post("/courses/{$course->slug}/exam/attempts/{$attempt->id}/submit", [
            'answers' => [
                [
                    'question_id' => $q->id,
                    'selected_option_ids' => [$correct->id],
                    'answer_text' => null,
                ],
            ],
        ])
        ->assertRedirect("/courses/{$course->slug}/exam");

    $attempt->refresh();

    expect($attempt->status)->toBe('submitted')
        ->and($attempt->score_points)->toBe(10)
        ->and($attempt->max_points)->toBe(10)
        ->and($attempt->needs_manual_review)->toBeFalse()
        ->and($attempt->passed)->toBeTrue();

    $answer = ExamAnswer::where('attempt_id', $attempt->id)->where('question_id', $q->id)->firstOrFail();
    expect($answer->is_correct)->toBeTrue()->and($answer->points_awarded)->toBe(10);
});

test('essay questions require manual review and do not set passed automatically', function () {
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->create();

    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => true,
    ]);

    $q = ExamQuestion::create([
        'exam_id' => $exam->id,
        'type' => 'essay',
        'prompt' => 'Explain polymorphism.',
        'points' => 10,
        'sort_order' => 0,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)->post("/courses/{$course->slug}/exam/start");

    $attempt = ExamAttempt::where('exam_id', $exam->id)->where('user_id', $student->id)->firstOrFail();

    $this->actingAs($student)
        ->post("/courses/{$course->slug}/exam/attempts/{$attempt->id}/submit", [
            'answers' => [
                [
                    'question_id' => $q->id,
                    'selected_option_ids' => [],
                    'answer_text' => 'It is the ability to take many forms.',
                ],
            ],
        ])
        ->assertRedirect("/courses/{$course->slug}/exam");

    $attempt->refresh();

    expect($attempt->status)->toBe('submitted')
        ->and($attempt->needs_manual_review)->toBeTrue()
        ->and($attempt->passed)->toBeNull();
});

test('expired attempt is blocked on submit', function () {
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->create();

    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => true,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    Carbon::setTestNow(now());

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'user_id' => $student->id,
        'attempt_number' => 1,
        'started_at' => now()->subMinutes(11),
        'expires_at' => now()->subSecond(),
        'status' => 'in_progress',
        'max_points' => 0,
        'needs_manual_review' => false,
    ]);

    $this->actingAs($student)
        ->post("/courses/{$course->slug}/exam/attempts/{$attempt->id}/submit", [
            'answers' => [],
        ])
        ->assertRedirect("/courses/{$course->slug}/exam")
        ->assertSessionHas('error');

    expect($attempt->fresh()->status)->toBe('expired');
});

test('instructor cannot publish an exam without questions', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();

    $this->actingAs($instructor)
        ->from("/instructor/courses/{$course->id}/exam")
        ->post("/instructor/courses/{$course->id}/exam", [
            'title' => 'Final Exam',
            'description' => null,
            'duration_minutes' => 10,
            'max_attempts' => 1,
            'pass_score' => 70,
            'is_published' => true,
        ])
        ->assertRedirect("/instructor/courses/{$course->id}/exam")
        ->assertSessionHasErrors('is_published');
});

test('instructor cannot publish single choice question without exactly one correct option', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => false,
    ]);
    $question = ExamQuestion::create([
        'exam_id' => $exam->id,
        'type' => 'single',
        'prompt' => 'Pick one.',
        'points' => 10,
        'sort_order' => 0,
    ]);
    ExamQuestionOption::create([
        'question_id' => $question->id,
        'text' => 'A',
        'is_correct' => false,
        'sort_order' => 0,
    ]);

    $this->actingAs($instructor)
        ->from("/instructor/courses/{$course->id}/exam")
        ->post("/instructor/courses/{$course->id}/exam", [
            'title' => 'Final Exam',
            'description' => null,
            'duration_minutes' => 10,
            'max_attempts' => 1,
            'pass_score' => 70,
            'is_published' => true,
        ])
        ->assertRedirect("/instructor/courses/{$course->id}/exam")
        ->assertSessionHasErrors('is_published');
});

test('student cannot start a published exam without questions', function () {
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->create();

    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => true,
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)
        ->post("/courses/{$course->slug}/exam/start")
        ->assertNotFound();
});

test('instructor can grade essay answers and finalize pass', function () {
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->create();

    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => true,
    ]);

    $q = ExamQuestion::create([
        'exam_id' => $exam->id,
        'type' => 'essay',
        'prompt' => 'Explain SOLID.',
        'points' => 10,
        'sort_order' => 0,
    ]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'user_id' => $student->id,
        'attempt_number' => 1,
        'started_at' => now(),
        'expires_at' => now()->addMinutes(10),
        'submitted_at' => now(),
        'status' => 'submitted',
        'score_points' => 0,
        'max_points' => 0,
        'needs_manual_review' => true,
        'passed' => null,
    ]);

    $answer = ExamAnswer::create([
        'attempt_id' => $attempt->id,
        'question_id' => $q->id,
        'answer_text' => '...',
        'points_awarded' => null,
        'is_correct' => null,
    ]);

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/exam/attempts/{$attempt->id}/grade", [
            'grades' => [
                [
                    'answer_id' => $answer->id,
                    'points_awarded' => 8,
                ],
            ],
        ])
        ->assertRedirect();

    $attempt->refresh();

    expect($attempt->needs_manual_review)->toBeFalse()
        ->and($attempt->max_points)->toBe(10)
        ->and($attempt->score_points)->toBe(8)
        ->and($attempt->passed)->toBeTrue();
});

test('instructor cannot grade an attempt before it is submitted', function () {
    $instructor = User::factory()->instructor()->create();
    $student = User::factory()->create();

    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $exam = CourseExam::create([
        'course_id' => $course->id,
        'title' => 'Final Exam',
        'description' => null,
        'duration_minutes' => 10,
        'max_attempts' => 1,
        'pass_score' => 70,
        'is_published' => true,
    ]);
    $question = ExamQuestion::create([
        'exam_id' => $exam->id,
        'type' => 'essay',
        'prompt' => 'Explain SOLID.',
        'points' => 10,
        'sort_order' => 0,
    ]);
    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'user_id' => $student->id,
        'attempt_number' => 1,
        'started_at' => now(),
        'expires_at' => now()->addMinutes(10),
        'status' => 'in_progress',
        'score_points' => null,
        'max_points' => 0,
        'needs_manual_review' => true,
        'passed' => null,
    ]);
    $answer = ExamAnswer::create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'answer_text' => '...',
        'points_awarded' => null,
        'is_correct' => null,
    ]);

    $this->actingAs($instructor)
        ->post("/instructor/courses/{$course->id}/exam/attempts/{$attempt->id}/grade", [
            'grades' => [
                [
                    'answer_id' => $answer->id,
                    'points_awarded' => 8,
                ],
            ],
        ])
        ->assertNotFound();
});
