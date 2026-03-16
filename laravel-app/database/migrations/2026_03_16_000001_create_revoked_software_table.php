<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revoked_software', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->string('software_name');
            $table->string('revoked_by')->nullable(); // admin username who revoked
            $table->timestamps();

            $table->unique(['user_name', 'software_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revoked_software');
    }
};
