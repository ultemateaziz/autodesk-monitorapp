<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licensed_machines', function (Blueprint $table) {
            $table->id();
            $table->string('machine_id', 64)->unique();   // SHA-256 fingerprint (32 hex chars)
            $table->string('hostname');                    // os.hostname() from agent
            $table->string('ip_address', 45)->nullable(); // IPv4/IPv6
            $table->string('license_key')->nullable();    // which AEPRO-XXXX key registered this
            $table->enum('status', ['pending', 'active', 'revoked'])->default('pending');
            $table->string('agent_token', 64)->nullable()->unique(); // token returned to agent
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->string('approved_by')->nullable();    // admin username who approved
            $table->string('revoked_by')->nullable();     // admin username who revoked
            $table->timestamps();

            $table->index('status');
            $table->index('hostname');
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licensed_machines');
    }
};
