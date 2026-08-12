<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position'); // Ketua, Bendahara, etc.
            $table->string('division'); // Inti, Ibadah, Pembangunan, etc.
            $table->string('photo_path')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['division', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_members');
    }
};
