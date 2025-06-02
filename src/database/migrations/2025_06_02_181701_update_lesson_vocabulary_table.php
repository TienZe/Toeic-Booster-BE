<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_vocabularies', function (Blueprint $table) {
            $table->string('word')->nullable();
            $table->foreignId('vocabulary_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lesson_vocabularies', function (Blueprint $table) {
            $table->dropColumn('word');
            $table->foreignId('vocabulary_id')->nullable(false)->change();
        });
    }
};
