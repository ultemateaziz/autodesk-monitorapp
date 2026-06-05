<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('user_name');
            $table->index('recorded_at');
            $table->index('application');
            $table->index(['user_name', 'recorded_at']);
            $table->index(['recorded_at', 'application']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_name']);
            $table->dropIndex(['recorded_at']);
            $table->dropIndex(['application']);
            $table->dropIndex(['user_name', 'recorded_at']);
            $table->dropIndex(['recorded_at', 'application']);
        });
    }
};
