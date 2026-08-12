<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Qurban extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'participant_name',
        'participant_nik',
        'participant_phone',
        'participant_address',
        'animal_type',
        'animal_weight',
        'animal_price',
        'is_shared',
        'share_count',
        'share_position',
        'share_group_id',
        'status',
        'year',
        'registration_date',
        'notes',
        'registered_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_date' => 'immutable_date',
            'animal_weight' => 'decimal:2',
            'animal_price' => 'decimal:2',
            'is_shared' => 'boolean',
            'share_count' => 'integer',
            'share_position' => 'integer',
            'year' => 'integer',
        ];
    }

    /**
     * Boot the model and auto-generate UUID.
     */
    protected static function booted(): void
    {
        static::creating(function (Qurban $qurban): void {
            if (empty($qurban->uuid)) {
                $qurban->uuid = (string) Str::uuid();
            }

            // Auto-generate share_group_id if shared but not yet assigned
            if ($qurban->is_shared && empty($qurban->share_group_id)) {
                $qurban->share_group_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who registered this qurban.
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Alias for registeredBy to support 'user' relationship calls.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Get distributions for this qurban.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(QurbanDistribution::class);
    }

    /**
     * Get formatted price accessor.
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp '.number_format($this->animal_price, 0, ',', '.'),
        );
    }

    /**
     * Get animal type label accessor.
     */
    protected function animalTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->animal_type) {
                'kambing' => 'Kambing',
                'domba' => 'Domba',
                'sapi' => 'Sapi',
                'kerbau' => 'Kerbau',
                'unta' => 'Unta',
                default => 'Unknown',
            },
        );
    }

    /**
     * Get status label accessor.
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'registered' => 'Terdaftar',
                'paid' => 'Lunas',
                'slaughtered' => 'Sudah Disembelih',
                'distributed' => 'Sudah Didistribusi',
                default => 'Unknown',
            },
        );
    }

    /**
     * Get share info accessor (e.g., "1/7 dari Sapi Grup A").
     */
    protected function shareInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->is_shared) {
                    return 'Individual';
                }

                $groupCode = substr($this->share_group_id, 0, 8);

                return "{$this->share_position}/{$this->share_count} dari {$this->animalTypeLabel} Grup {$groupCode}";
            },
        );
    }

    /**
     * Scope to filter by animal type.
     */
    public function scopeByAnimalType(Builder $query, string $type): void
    {
        $query->where('animal_type', $type);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeByYear(Builder $query, int $year): void
    {
        $query->where('year', $year);
    }

    /**
     * Scope to filter shared qurbans only.
     */
    public function scopeShared(Builder $query): void
    {
        $query->where('is_shared', true);
    }

    /**
     * Scope to filter individual qurbans only.
     */
    public function scopeIndividual(Builder $query): void
    {
        $query->where('is_shared', false);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope to filter by share group.
     */
    public function scopeByShareGroup(Builder $query, string $groupId): void
    {
        $query->where('share_group_id', $groupId);
    }

    /**
     * Scope to order by date descending.
     */
    public function scopeLatest(Builder $query): void
    {
        $query->orderBy('registration_date', 'desc');
    }

    /**
     * Scope to filter current year.
     */
    public function scopeCurrentYear(Builder $query): void
    {
        $query->where('year', now()->year);
    }
}
