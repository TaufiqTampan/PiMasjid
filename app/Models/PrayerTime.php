<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class PrayerTime extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'hijri_date',
        'subuh',
        'sunrise',
        'dhuhr',
        'asr',
        'maghrib',
        'isha',
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
            'subuh' => 'datetime:H:i',
            'sunrise' => 'datetime:H:i',
            'dhuhr' => 'datetime:H:i',
            'asr' => 'datetime:H:i',
            'maghrib' => 'datetime:H:i',
            'isha' => 'datetime:H:i',
        ];
    }

    /**
     * Get formatted date accessor.
     */
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date?->translatedFormat('l, d M Y'),
        );
    }

    /**
     * Get formatted Subuh time accessor.
     */
    protected function formattedSubuh(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->subuh?->format('H:i'),
        );
    }

    /**
     * Get formatted Sunrise time accessor.
     */
    protected function formattedSunrise(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sunrise?->format('H:i'),
        );
    }

    /**
     * Get formatted Dhuhr time accessor.
     */
    protected function formattedDhuhr(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->dhuhr?->format('H:i'),
        );
    }

    /**
     * Get formatted Asr time accessor.
     */
    protected function formattedAsr(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->asr?->format('H:i'),
        );
    }

    /**
     * Get formatted Maghrib time accessor.
     */
    protected function formattedMaghrib(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->maghrib?->format('H:i'),
        );
    }

    /**
     * Get formatted Isha time accessor.
     */
    protected function formattedIsha(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->isha?->format('H:i'),
        );
    }

    /**
     * Scope to filter today's prayer times.
     */
    public function scopeToday(Builder $query): void
    {
        $query->whereDate('date', today());
    }

    /**
     * Scope to filter by specific month and year.
     */
    public function scopeByMonth(Builder $query, int $month, int $year): void
    {
        $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    /**
     * Scope to order by date descending.
     */
    public function scopeLatest(Builder $query): void
    {
        $query->orderBy('date', 'desc');
    }
}
