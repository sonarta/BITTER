<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('answer_id')->constrained('exam_answers')->cascadeOnDelete();
            $table->foreignUlid('option_id')->constrained('exam_question_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['answer_id', 'option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answer_options');
    }
};
