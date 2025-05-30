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
        Schema::create('toeic_chat_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toeic_test_attempt_id')->constrained('toeic_test_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('toeic_chat_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toeic_chat_history_id')->constrained('toeic_chat_histories')->onDelete('cascade');
            $table->mediumText('content_serialized');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toeic_chat_contents');
        Schema::dropIfExists('toeic_chat_histories');
    }
};
