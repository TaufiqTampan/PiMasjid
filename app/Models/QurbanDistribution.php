<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QurbanDistribution extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'qurban_id',
        'recipient_name',
        'recipient_type',
        'meat_kg',
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
            'meat_kg' => 'decimal:2',
            'qurban_id' => 'integer',
        ];
    }

    /**
     * Boot the model and auto-generate UUID.
     */
    protected static function booted(): void
    {
        static::creating(function (QurbanDistribution $distribution): void {
            if (empty($distribution->uuid)) {
                $distribution->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the qurban for this distribution.
     */
    public function qurban(): BelongsTo
    {
        return $this->belongsTo(Qurban::class);
    }

    /**
     * Get the user who distributed this.
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
     * Get recipient type label accessor.
     */
    protected function recipientTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->recipient_type) {
                'mustahik' => 'Mustahik (8 Asnaf)',
                'aqiqah' => 'Aqiqah',
                'participant' => 'Peserta Qurban',
                'masjid' => 'Masjid',
                default => 'Unknown',
            },
        );
    }

    /**
     * Get formatted meat weight accessor.
     */
    protected function formattedMeatKg(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->meat_kg, 2, ',', '.').' kg',
        );
    }

    /**
     * Scope to filter by qurban.
     */
    public function scopeByQurban(Builder $query, int $qurbanId): void
    {
        $query->where('qurban_id', $qurbanId);
    }

    /**
     * Scope to filter by recipient type.
     */
    public function scopeByRecipientType(Builder $query, string $type): void
    {
        $query->where('recipient_type', $type);
    }

    /**
     * Scope to order by date descending.
     */
    public function scopeLatest(Builder $query): void
    {
        $query->orderBy('date', 'desc');
    }
}
