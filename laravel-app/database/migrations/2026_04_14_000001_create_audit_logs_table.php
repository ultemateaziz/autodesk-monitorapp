<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('performed_by');          // Admin username who did the action
            $table->string('action');                // e.g. 'license_assigned', 'user_created'
            $table->string('target_user')->nullable(); // Affected user/machine
            $table->text('description');             // Human-readable description
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
