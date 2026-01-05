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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            
            // Kelembaban tanah 4 petak sawah (0-100%)
            $table->integer('petak1_moisture')->default(0);
            $table->integer('petak2_moisture')->default(0);
            $table->integer('petak3_moisture')->default(0);
            $table->integer('petak4_moisture')->default(0);
            
            // Level air dari sensor ultrasonik (dalam cm)
            $table->float('water_main')->nullable();  // Saluran utama
            $table->float('water_mid')->nullable();   // Saluran tengah
            $table->float('water_tank')->nullable();  // Tandon
            
            // Status komponen
            $table->boolean('pump_status')->default(false);
            $table->integer('gate_main')->default(0);  // 0 = tutup, 90 = buka
            $table->integer('gate1')->default(0);
            $table->integer('gate2')->default(0);
            $table->integer('gate3')->default(0);
            $table->integer('gate4')->default(0);
            
            // Status sistem
            $table->string('system_status')->default('Stabil');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
