<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_exams', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title')->default('Final Exam');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->unsignedSmallInteger('max_attempts')->default(1);
            $table->unsignedSmallInteger('pass_score')->default(70);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();

            $table->unique(['course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_exams');
    }
};

