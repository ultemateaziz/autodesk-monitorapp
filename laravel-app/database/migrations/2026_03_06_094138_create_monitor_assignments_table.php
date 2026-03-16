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
        Schema::create('monitor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leader_id')->constrained('users')->onDelete('cascade');
            $table->string('monitored_user_name'); // The username as it appears in activity logs
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['leader_id', 'monitored_user_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitor_assignments');
    }
};
