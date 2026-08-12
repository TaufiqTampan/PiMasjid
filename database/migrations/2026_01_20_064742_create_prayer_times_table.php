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
        Schema::create('prayer_times', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->comment('Gregorian date');
            $table->string('hijri_date', 50)->nullable()->comment('Hijri date (e.g., 15 Rajab 1448)');
            $table->time('subuh')->comment('Subuh/Fajr prayer time');
            $table->time('sunrise')->comment('Sunrise time');
            $table->time('dhuhr')->comment('Dhuhr/Zuhr prayer time');
            $table->time('asr')->comment('Asr prayer time');
            $table->time('maghrib')->comment('Maghrib prayer time');
            $table->time('isha')->comment('Isha prayer time');
            $table->timestamps();

            // Indexes for performance
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_times');
    }
};
