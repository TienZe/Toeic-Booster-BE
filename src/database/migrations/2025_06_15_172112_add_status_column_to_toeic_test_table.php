<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toeic_tests', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('toeic_tests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
