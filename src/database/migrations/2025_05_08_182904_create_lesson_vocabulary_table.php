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
        Schema::create('lesson_vocabularies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->foreignId('vocabulary_id')->constrained('vocabularies')->onDelete('cascade');

            $table->string('thumbnail')->nullable();
            $table->string('thumbnail_public_id')->nullable();

            $table->string('part_of_speech')->nullable();
            $table->string('meaning')->nullable();
            $table->string('definition')->nullable();
            $table->string('pronunciation')->nullable();

            $table->string('pronunciation_audio')->nullable();
            $table->string('pronunciation_audio_public_id')->nullable();

            $table->string('example')->nullable();
            $table->string('example_meaning')->nullable();
            $table->string('example_audio')->nullable();
            $table->string('example_audio_public_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_vocabulary');
    }
};
