<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_exam_id')->constrained('lesson_exams')->onDelete('cascade');
            $table->foreignId('lesson_vocabulary_id')->constrained('lesson_vocabularies')->onDelete('cascade');
            $table->boolean('is_correct');
            $table->timestamps();

            $table->unique(['lesson_exam_id', 'lesson_vocabulary_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_exam_answers');
    }
};
