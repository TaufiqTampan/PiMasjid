<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_path',
        'response_time_ms',
        'memory_usage_mb',
        'query_count',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the user who made the request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter logs from today.
     */
    public function scopeToday(Builder $query): void
    {
        $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Scope to filter logs from the last N days.
     */
    public function scopeLastDays(Builder $query, int $days = 7): void
    {
        $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to filter logs from the current week.
     */
    public function scopeThisWeek(Builder $query): void
    {
        $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope to filter by request path.
     */
    public function scopeByPath(Builder $query, string $path): void
    {
        $query->where('request_path', $path);
    }

    /**
     * Get average response time for the given query.
     */
    public static function averageResponseTime(?Builder $query = null): float
    {
        $query = $query ?? static::query();

        return round($query->avg('response_time_ms'), 2);
    }

    /**
     * Get average memory usage for the given query.
     */
    public static function averageMemoryUsage(?Builder $query = null): float
    {
        $query = $query ?? static::query();

        return round($query->avg('memory_usage_mb'), 2);
    }

    /**
     * Get average query count for the given query.
     */
    public static function averageQueryCount(?Builder $query = null): float
    {
        $query = $query ?? static::query();

        return round($query->avg('query_count'), 2);
    }
}
