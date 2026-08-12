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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->string('item_name', 200)->comment('Name of the item/need');
            $table->integer('target_qty')->default(1)->comment('Target quantity needed');
            $table->integer('fulfilled_qty')->default(0)->comment('Quantity already fulfilled');
            $table->decimal('unit_price', 15, 2)->comment('Price per unit');
            $table->enum('status', ['active', 'pending', 'completed', 'cancelled'])->default('active')->comment('Wishlist status');
            $table->text('description')->nullable()->comment('Item description/notes');
            $table->timestamps();

            // Indexes for performance
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
