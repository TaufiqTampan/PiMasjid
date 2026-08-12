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
        Schema::create('qurban_distributions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Qurban Reference
            $table->foreignId('qurban_id')->constrained('qurbans')->onDelete('cascade');

            // Recipient Data
            $table->string('recipient_name');
            $table->enum('recipient_type', ['mustahik', 'aqiqah', 'participant', 'masjid'])->index();

            // Distribution Info
            $table->decimal('meat_kg', 10, 2); // Berat daging dalam kg

            // Date
            $table->date('date')->index();

            // Notes
            $table->text('notes')->nullable();

            // Distributor
            $table->foreignId('distributed_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            // Composite indexes
            $table->index(['qurban_id', 'recipient_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qurban_distributions');
    }
};
