<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('toeic_chat_histories', function (Blueprint $table) {
            $table->integer('indexed_question_id')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table('toeic_chat_histories', function (Blueprint $table) {
            $table->dropColumn('indexed_question_id');
        });
    }
};
