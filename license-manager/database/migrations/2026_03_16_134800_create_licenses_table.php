<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('license_key')->unique();
            $blueprint->string('tier'); // 7D, 15D, 6M, 1Y
            $blueprint->boolean('is_active')->default(false);
            $blueprint->dateTime('expires_at')->nullable();
            $blueprint->string('machine_id')->nullable(); // Unique hardware ID
            $blueprint->string('machine_name')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
