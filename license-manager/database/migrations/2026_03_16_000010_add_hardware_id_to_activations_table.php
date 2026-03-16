<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            // hardware_id = Windows MachineGUID (real hardware fingerprint)
            // machine_id  = os.hostname() (human-readable machine name)
            $table->string('hardware_id')->nullable()->after('machine_id');
            $table->string('machine_name')->nullable()->after('hardware_id');
        });

        Schema::table('licenses', function (Blueprint $table) {
            // Also track hardware_id on the license itself
            $table->string('hardware_id')->nullable()->after('machine_name');
        });
    }

    public function down(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            $table->dropColumn(['hardware_id', 'machine_name']);
        });
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('hardware_id');
        });
    }
};
