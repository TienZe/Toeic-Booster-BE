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
        Schema::create('vocabularies', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->string('thumbnail')->nullable();
            $table->string('thumbnail_public_id')->nullable();
            $table->string('part_of_speech');
            $table->string('meaning');
            $table->text('definition')->nullable();
            $table->string('pronunciation')->nullable();
            $table->string('pronunciation_audio')->nullable();
            $table->string('pronunciation_audio_public_id')->nullable();
            $table->text('example')->nullable();
            $table->text('example_meaning')->nullable();
            $table->string('example_audio')->nullable();
            $table->string('example_audio_public_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabularies');
    }
};
