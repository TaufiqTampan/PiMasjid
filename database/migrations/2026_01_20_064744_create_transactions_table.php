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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Public identifier for sharing transaction links');
            $table->enum('type', ['income', 'expense'])->comment('Transaction type: income or expense');
            $table->string('category', 100)->comment('Category (e.g., Kotak Jumat, Renovasi, Operasional)');
            $table->decimal('amount', 15, 2)->comment('Transaction amount');
            $table->text('description')->nullable()->comment('Transaction description/notes');
            $table->string('proof_image_path')->nullable()->comment('Path to proof image (REQUIRED for expenses)');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->comment('User who verified/recorded this transaction');
            $table->date('date')->comment('Transaction date');

            // Approval workflow fields
            $table->enum('status', ['approved', 'pending', 'rejected'])
                ->default('approved')
                ->comment('Approval status for large expenses');
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete()
                ->comment('User who approved this transaction (Ketua)');
            $table->timestamp('approved_at')->nullable()
                ->comment('Approval timestamp');
            $table->text('rejection_reason')->nullable()
                ->comment('Reason for rejection if status is rejected');

            $table->timestamps();

            // Indexes for performance
            $table->index(['type', 'date']);
            $table->index('category');
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
