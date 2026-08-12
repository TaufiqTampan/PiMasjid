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
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200)->comment('Slide title');
            $table->text('content')->nullable()->comment('Slide content/description');
            $table->string('image_path')->nullable()->comment('Path to slide image');
            $table->boolean('is_active')->default(true)->comment('Whether slide is active');
            $table->integer('order')->default(0)->comment('Display order (ascending)');
            $table->timestamps();

            // Indexes for performance
            $table->index(['is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
