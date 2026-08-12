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
        Schema::create('zakat_distributions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Mustahik Data
            $table->string('mustahik_name');
            $table->enum('mustahik_category', [
                'fakir', 'miskin', 'amil', 'muallaf',
                'riqab', 'gharim', 'sabilillah', 'ibnu_sabil',
            ])->index();

            // Distribution Info
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['uang', 'beras'])->default('uang');
            $table->decimal('rice_kg', 10, 2)->nullable();

            // Year & Date
            $table->integer('year')->index();
            $table->date('date')->index();

            // Notes
            $table->text('notes')->nullable();

            // Distributor
            $table->foreignId('distributed_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            // Composite indexes
            $table->index(['mustahik_category', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zakat_distributions');
    }
};
