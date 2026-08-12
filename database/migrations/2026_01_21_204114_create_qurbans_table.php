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
        Schema::create('qurbans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Participant Data
            $table->string('participant_name');
            $table->string('participant_nik')->nullable()->index();
            $table->string('participant_phone');
            $table->text('participant_address')->nullable();

            // Animal Info
            $table->enum('animal_type', ['kambing', 'domba', 'sapi', 'kerbau', 'unta'])->index();
            $table->decimal('animal_weight', 10, 2)->nullable(); // Estimasi berat (kg)
            $table->decimal('animal_price', 15, 2); // Harga

            // Share / Patungan System
            $table->boolean('is_shared')->default(false)->index();
            $table->integer('share_count')->default(1); // Berapa orang patungan (max 7 untuk sapi)
            $table->integer('share_position')->nullable(); // Posisi ke berapa (1-7)
            $table->string('share_group_id')->nullable()->index(); // UUID untuk grup patungan

            // Status
            $table->enum('status', ['registered', 'paid', 'slaughtered', 'distributed'])->default('registered')->index();

            // Year & Date
            $table->integer('year')->index(); // Tahun hijriah
            $table->date('registration_date')->index();

            // Notes
            $table->text('notes')->nullable();

            // Registrar
            $table->foreignId('registered_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            // Composite indexes
            $table->index(['animal_type', 'year']);
            $table->index(['status', 'year']);
            $table->index(['share_group_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qurbans');
    }
};
