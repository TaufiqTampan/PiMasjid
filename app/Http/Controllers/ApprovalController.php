<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    /**
     * Display pending approvals for Ketua.
     */
    public function index(): Response
    {
        Gate::authorize('approve_transaction');

        $threshold = config('masjid.approval_threshold', 1000000);

        $pendingTransactions = Transaction::with('verifiedBy')
            ->where('status', 'pending')
            ->where('amount', '>', $threshold)
            ->latest()
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'uuid' => $transaction->uuid,
                'category' => $transaction->category,
                'amount' => $transaction->amount,
                'formatted_amount' => $transaction->formatted_amount,
                'description' => $transaction->description,
                'proof_url' => $transaction->proof_url,
                'date' => $transaction->date->format('Y-m-d'),
                'formatted_date' => $transaction->formatted_date,
                'verified_by_name' => $transaction->verifiedBy?->name,
            ]);

        return Inertia::render('Approvals/Index', [
            'pendingTransactions' => $pendingTransactions,
            'threshold' => $threshold,
        ]);
    }

    /**
     * Approve a pending transaction.
     */
    public function approve(Transaction $transaction)
    {
        Gate::authorize('approve_transaction');

        // Ensure transaction is pending
        if ($transaction->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Transaksi ini tidak dalam status pending.');
        }

        // Update transaction status
        $transaction->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Transaksi berhasil disetujui!');
    }

    /**
     * Reject a pending transaction.
     */
    public function reject(Request $request, Transaction $transaction)
    {
        Gate::authorize('approve_transaction');

        // Validate rejection reason
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        // Ensure transaction is pending
        if ($transaction->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Transaksi ini tidak dalam status pending.');
        }

        // Update transaction status
        $transaction->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()
            ->with('success', 'Transaksi ditolak.');
    }
}
