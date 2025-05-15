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
        // toeic_tests
        Schema::create('toeic_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // question_groups
        Schema::create('question_groups', function (Blueprint $table) {
            $table->id();
            $table->string('part');
            $table->text('transcript')->nullable();
            $table->foreignId('toeic_test_id')->constrained('toeic_tests')->onDelete('cascade');
            $table->timestamps();
        });

        // question_medias
        Schema::create('question_medias', function (Blueprint $table) {
            $table->id();
            $table->string('file_url');
            $table->string('file_public_id');
            $table->string('file_type');
            $table->integer('order');
            $table->foreignId('question_group_id')->constrained('question_groups')->onDelete('cascade');
            $table->timestamps();
        });

        // questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->integer('question_number');
            $table->text('explanation')->nullable();
            $table->text('A')->nullable();
            $table->text('B')->nullable();
            $table->text('C')->nullable();
            $table->text('D')->nullable();
            $table->enum('correct_answer', ['A', 'B', 'C', 'D']);
            $table->foreignId('question_group_id')->constrained('question_groups')->onDelete('cascade');
            $table->timestamps();
        });

        // toeic_test_attempts
        Schema::create('toeic_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('toeic_test_id')->constrained('toeic_tests')->onDelete('cascade');
            $table->integer('score')->nullable();
            $table->integer('listening_score')->nullable();
            $table->integer('reading_score')->nullable();
            $table->string('selected_parts')->nullable();
            $table->unsignedInteger('taken_time')->nullable();
            $table->timestamps();
        });

        // user_answers
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('toeic_test_attempt_id')->constrained('toeic_test_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->enum('choice', ['A', 'B', 'C', 'D']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_answers');
        Schema::dropIfExists('toeic_test_attempts');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_medias');
        Schema::dropIfExists('question_groups');
        Schema::dropIfExists('toeic_tests');
    }
};
