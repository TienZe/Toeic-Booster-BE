<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('toeic_chat_contents', function (Blueprint $table) {
            $table->boolean('hidden')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('toeic_chat_contents', function (Blueprint $table) {
            $table->dropColumn('hidden');
        });
    }
};
