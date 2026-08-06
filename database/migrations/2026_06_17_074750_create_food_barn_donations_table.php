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
        Schema::create('food_barn_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_barn_program_id')
                  ->nullable()
                  ->constrained('food_barn_programs')
                  ->onDelete('cascade');
            $table->string('donor_name');
            $table->string('donor_phone');
            $table->string('donation_type'); // uang, barang
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('items')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('proof_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_barn_donations');
    }
};
