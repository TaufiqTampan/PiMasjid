<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Honeypot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only inspect state-mutating requests (POST, PUT, PATCH, DELETE)
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        // 1. Check honeypot field (must be empty for human users)
        $honeypotField = 'website_hp';
        if ($request->filled($honeypotField)) {
            if ($request->wantsJson() && ! $request->header('X-Inertia')) {
                return response()->json(['message' => 'Spam detected.'], 422);
            }

            return back()->withErrors(['spam' => 'Spam detected. Submission rejected.']);
        }

        // 2. Check submission speed timestamp (if submitted in under 1 second = automated bot)
        $timestampField = 'hp_time';
        if ($request->has($timestampField)) {
            $submittedTime = (int) $request->input($timestampField);
            $currentTime = time();

            // If submitted in less than 1 second
            if ($submittedTime > 0 && ($currentTime - $submittedTime) < 1) {
                if ($request->wantsJson() && ! $request->header('X-Inertia')) {
                    return response()->json(['message' => 'Submission too fast.'], 422);
                }

                return back()->withErrors(['spam' => 'Submission submitted too quickly.']);
            }
        }

        return $next($request);
    }
}
