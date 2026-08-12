<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the Transaction "creating" event.
     */
    public function creating(Transaction $transaction): void
    {
        $threshold = config('masjid.approval_threshold', 1000000);

        // Auto-approve all income transactions
        if ($transaction->type === 'income') {
            $transaction->status = 'approved';
            $transaction->approved_at = now();
            $transaction->approved_by = auth()->id();

            return;
        }

        // For expenses, check threshold
        if ($transaction->type === 'expense') {
            if ($transaction->amount > $threshold) {
                // Requires approval
                $transaction->status = 'pending';
            } else {
                // Auto-approve small expenses
                $transaction->status = 'approved';
                $transaction->approved_at = now();
                $transaction->approved_by = auth()->id();
            }
        }
    }

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
