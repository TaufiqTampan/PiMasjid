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
        Schema::create('food_barn_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_barn_program_id')
                  ->constrained('food_barn_programs')
                  ->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->text('address');
            $table->integer('family_members');
            $table->text('reason');
            $table->string('status')->default('pending'); // pending, approved, rejected, distributed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_barn_requests');
    }
};
