<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Optimized settings fetch: key => value pairs
        // Cache for 60 minutes to improve performance
        $settings = \Illuminate\Support\Facades\Cache::remember('global_settings', 60 * 60, function () {
            return Setting::all()->pluck('value', 'key');
        });

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'settings' => $settings,
            'isImpersonating' => session()->has('impersonated_by'),
            // Pending approvals count for Ketua badge (performance optimization)
            'pendingApprovalsCount' => $user && $user->role === 'ketua'
                ? \App\Models\Transaction::pending()->count()
                : 0,
        ];
    }
}
