<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revoked_software', function (Blueprint $table) {
            // 'suspended' = temporary, restorable | 'permanent' = no restore
            $table->enum('type', ['suspended', 'permanent'])->default('suspended')->after('revoked_by');
        });
    }

    public function down(): void
    {
        Schema::table('revoked_software', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
