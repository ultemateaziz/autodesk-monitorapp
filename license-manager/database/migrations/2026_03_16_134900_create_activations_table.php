<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activations', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('license_id')->constrained()->onDelete('cascade');
            $blueprint->string('machine_id');
            $blueprint->string('ip_address');
            $blueprint->dateTime('last_pulse');
            $blueprint->enum('status', ['active', 'locked', 'expired'])->default('active');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activations');
    }
};
