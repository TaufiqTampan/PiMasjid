<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Zakat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'muzakki_name',
        'muzakki_nik',
        'muzakki_phone',
        'muzakki_address',
        'type',
        'amount',
        'payment_type',
        'rice_kg',
        'person_count',
        'year',
        'date',
        'notes',
        'collected_by',
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
            'rice_kg' => 'decimal:2',
            'person_count' => 'integer',
            'year' => 'integer',
        ];
    }

    /**
     * Boot the model and auto-generate UUID.
     */
    protected static function booted(): void
    {
        static::creating(function (Zakat $zakat): void {
            if (empty($zakat->uuid)) {
                $zakat->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who collected this zakat.
     */
    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Alias for collectedBy to support 'user' relationship calls.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
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
     * Get type label accessor.
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->type) {
                'fitrah' => 'Zakat Fitrah',
                'mal' => 'Zakat Mal',
                'profesi' => 'Zakat Profesi',
                default => 'Unknown',
            },
        );
    }

    /**
     * Get payment type label accessor.
     */
    protected function paymentTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->payment_type) {
                'uang' => 'Uang',
                'beras' => 'Beras',
                default => 'Unknown',
            },
        );
    }

    /**
     * Scope to filter Zakat Fitrah.
     */
    public function scopeFitrah(Builder $query): void
    {
        $query->where('type', 'fitrah');
    }

    /**
     * Scope to filter Zakat Mal.
     */
    public function scopeMal(Builder $query): void
    {
        $query->where('type', 'mal');
    }

    /**
     * Scope to filter Zakat Profesi.
     */
    public function scopeProfesi(Builder $query): void
    {
        $query->where('type', 'profesi');
    }

    /**
     * Scope to filter by year.
     */
    public function scopeByYear(Builder $query, int $year): void
    {
        $query->where('year', $year);
    }

    /**
     * Scope to filter by payment type.
     */
    public function scopeByPaymentType(Builder $query, string $paymentType): void
    {
        $query->where('payment_type', $paymentType);
    }

    /**
     * Scope to filter by muzakki NIK.
     */
    public function scopeByNik(Builder $query, string $nik): void
    {
        $query->where('muzakki_nik', $nik);
    }

    /**
     * Scope to order by date descending.
     */
    public function scopeLatest(Builder $query): void
    {
        $query->orderBy('date', 'desc');
    }

    /**
     * Scope to filter current year.
     */
    public function scopeCurrentYear(Builder $query): void
    {
        $query->where('year', now()->year);
    }
}
