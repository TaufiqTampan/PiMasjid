<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_name',
        'target_qty',
        'fulfilled_qty',
        'unit_price',
        'status',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_qty' => 'integer',
            'fulfilled_qty' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    /**
     * Get total target amount accessor.
     */
    protected function totalTarget(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->target_qty * $this->unit_price,
        );
    }

    /**
     * Get total fulfilled amount accessor.
     */
    protected function totalFulfilled(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fulfilled_qty * $this->unit_price,
        );
    }

    /**
     * Get remaining quantity accessor.
     */
    protected function remainingQty(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, $this->target_qty - $this->fulfilled_qty),
        );
    }

    /**
     * Get progress percentage accessor.
     */
    protected function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->target_qty > 0
                ? round(($this->fulfilled_qty / $this->target_qty) * 100, 2)
                : 0,
        );
    }

    /**
     * Get formatted unit price accessor.
     */
    protected function formattedUnitPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp '.number_format($this->unit_price, 0, ',', '.'),
        );
    }

    /**
     * Get formatted total target accessor.
     */
    protected function formattedTotalTarget(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp '.number_format($this->total_target, 0, ',', '.'),
        );
    }

    /**
     * Get formatted total fulfilled accessor.
     */
    protected function formattedTotalFulfilled(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp '.number_format($this->total_fulfilled, 0, ',', '.'),
        );
    }

    /**
     * Get status label accessor.
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'active' => 'Aktif',
                'pending' => 'Menunggu',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                default => 'Unknown',
            },
        );
    }

    /**
     * Scope to filter active wishlists.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope to filter completed wishlists.
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', 'completed');
    }

    /**
     * Scope to filter pending wishlists.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /**
     * Scope to order by creation date descending.
     */
    public function scopeLatest(Builder $query): void
    {
        $query->orderBy('created_at', 'desc');
    }
}
