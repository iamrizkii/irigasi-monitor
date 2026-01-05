<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_settings', function (Blueprint $table) {
            $table->id();

            // Mode operasi: auto (ESP32 kontrol) atau manual (web kontrol)
            $table->enum('mode', ['auto', 'manual'])->default('auto');

            // Perintah kontrol dari web (hanya aktif saat mode manual)
            $table->boolean('pump_command')->default(false);
            $table->integer('gate_main_command')->default(0);
            $table->integer('gate1_command')->default(0);
            $table->integer('gate2_command')->default(0);
            $table->integer('gate3_command')->default(0);
            $table->integer('gate4_command')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_settings');
    }
};
