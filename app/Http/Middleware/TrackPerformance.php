<?php

namespace App\Http\Middleware;

use App\Models\PerformanceLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackPerformance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Record start metrics
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true) / 1024 / 1024; // Convert to MB

        // Enable query log to count queries
        DB::enableQueryLog();

        // Process the request
        $response = $next($request);

        // Calculate metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true) / 1024 / 1024;

        $responseTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        $memoryUsage = round($endMemory, 2);
        $queryCount = count(DB::getQueryLog());

        // Log performance data (only for authenticated requests to reduce noise)
        if (auth()->check()) {
            try {
                PerformanceLog::create([
                    'request_path' => $request->path(),
                    'response_time_ms' => $responseTime,
                    'memory_usage_mb' => $memoryUsage,
                    'query_count' => $queryCount,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                // Silently fail to avoid breaking the request
                // In production, you might want to log this to a separate error log
            }
        }

        return $response;
    }
}
