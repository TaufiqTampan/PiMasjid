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
        Schema::create('zakats', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Muzakki Data
            $table->string('muzakki_name');
            $table->string('muzakki_nik')->nullable()->index();
            $table->string('muzakki_phone')->nullable();
            $table->text('muzakki_address')->nullable();

            // Zakat Info
            $table->enum('type', ['fitrah', 'mal', 'profesi'])->index();
            $table->decimal('amount', 15, 2); // Jumlah dalam rupiah
            $table->enum('payment_type', ['uang', 'beras'])->default('uang');
            $table->decimal('rice_kg', 10, 2)->nullable(); // Jika bayar beras
            $table->integer('person_count')->nullable(); // Jumlah jiwa untuk fitrah

            // Year & Date
            $table->integer('year')->index(); // Tahun hijriah
            $table->date('date')->index();

            // Notes
            $table->text('notes')->nullable();

            // Collector
            $table->foreignId('collected_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['type', 'year']);
            $table->index(['year', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zakats');
    }
};
