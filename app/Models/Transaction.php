<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'type',
        'category',
        'amount',
        'description',
        'proof_image_path',
        'cloudinary_public_id',
        'verified_by',
        'date',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Boot the model and auto-generate UUID.
     */
    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction): void {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }

            // Validate proof_image_path is required for expenses
            if ($transaction->type === 'expense' && empty($transaction->proof_image_path)) {
                throw new \InvalidArgumentException('Proof image is required for expense transactions.');
            }
        });
    }

    /**
     * Get the user who verified this transaction.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Alias for verifiedBy to support 'user' relationship calls.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who approved this transaction.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get formatted amount accessor.
     */
    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp '.number_format($this->amount, 0, ',', '.'),
        );
    }

    /**
     * Get status label accessor.
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->type) {
                'income' => 'Pemasukan',
                'expense' => 'Pengeluaran',
                default => 'Unknown',
            },
        );
    }

    /**
     * Get proof image URL accessor.
     */
    protected function proofUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->proof_image_path
                ? (str_starts_with($this->proof_image_path, 'http') ? $this->proof_image_path : asset('storage/'.$this->proof_image_path))
                : null,
        );
    }

    /**
     * Scope to filter income transactions.
     */
    public function scopeIncome(Builder $query): void
    {
        $query->where('type', 'income');
    }

    /**
     * Scope to filter expense transactions.
     */
    public function scopeExpense(Builder $query): void
    {
        $query->where('type', 'expense');
    }

    /**
     * Scope to filter transactions for the current month.
     */
    public function scopeThisMonth(Builder $query): void
    {
        $query->whereYear('date', now()->year)
            ->whereMonth('date', now()->month);
    }

    /**
     * Scope to filter transactions for the current year.
     */
    public function scopeThisYear(Builder $query): void
    {
        $query->whereYear('date', now()->year);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    /**
     * Scope to order by date descending.
     */
    public function scopeLatest(Builder $query): void
    {
        $query->orderBy('date', 'desc');
    }

    /**
     * Scope to filter approved transactions.
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    /**
     * Scope to filter pending transactions.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /**
     * Scope to filter rejected transactions.
     */
    public function scopeRejected(Builder $query): void
    {
        $query->where('status', 'rejected');
    }
}
