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
        Schema::create('toeic_test_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category');
        });

        Schema::table('toeic_tests', function (Blueprint $table) {
            $table->foreignId('toeic_test_category_id')
                ->nullable()
                ->constrained('toeic_test_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toeic_tests', function (Blueprint $table) {
            $table->dropForeign(['toeic_test_category_id']);
            $table->dropColumn('toeic_test_category_id');
        });

        Schema::dropIfExists('toeic_test_categories');
    }
};
