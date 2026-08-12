<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ZakatDistribution extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'mustahik_name',
        'mustahik_category',
        'amount',
        'type',
        'rice_kg',
        'year',
        'date',
        'notes',
        'distributed_by',
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
            'year' => 'integer',
        ];
    }

    /**
     * Boot the model and auto-generate UUID.
     */
    protected static function booted(): void
    {
        static::creating(function (ZakatDistribution $distribution): void {
            if (empty($distribution->uuid)) {
                $distribution->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who distributed this zakat.
     */
    public function distributedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    /**
     * Alias for distributedBy to support 'user' relationship calls.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
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
     * Get asnaf/category label accessor.
     */
    protected function categoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->mustahik_category) {
                'fakir' => 'Fakir (Tidak punya harta/penghasilan)',
                'miskin' => 'Miskin (Penghasilan kurang)',
                'amil' => 'Amil (Pengelola zakat)',
                'muallaf' => 'Muallaf (Mualaf)',
                'riqab' => 'Riqab (Memerdekakan budak)',
                'gharim' => 'Gharim (Berhutang)',
                'sabilillah' => 'Sabilillah (Jihad fi sabilillah)',
                'ibnu_sabil' => 'Ibnu Sabil (Musafir)',
                default => 'Unknown',
            },
        );
    }

    /**
     * Scope to filter by asnaf category.
     */
    public function scopeByCategory(Builder $query, string $category): void
    {
        $query->where('mustahik_category', $category);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeByYear(Builder $query, int $year): void
    {
        $query->where('year', $year);
    }

    /**
     * Scope to filter by type (uang/beras).
     */
    public function scopeByType(Builder $query, string $type): void
    {
        $query->where('type', $type);
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
