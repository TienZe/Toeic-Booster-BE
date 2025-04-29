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
        Schema::create('collection_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name');
        });

        Schema::create('collection_collection_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->onDelete('cascade');
            $table->foreignId('collection_tag_id')->constrained()->onDelete('cascade');

            $table->unique(['collection_id', 'collection_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_collection_tag');
        Schema::dropIfExists('collection_tags');
    }
};
