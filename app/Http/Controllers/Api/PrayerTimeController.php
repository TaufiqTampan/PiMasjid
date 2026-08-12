<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PrayerTimeController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $lat = $request->input('latitude');
        $long = $request->input('longitude');
        $date = now()->format('Y-m-d');

        // Cache key specific to location (rounded) and date
        // Rounding to 3 decimals (~110m) to group nearby users and improve cache hit rate
        $latKey = round($lat, 3);
        $longKey = round($long, 3);
        $cacheKey = "prayer_times_{$latKey}_{$longKey}_{$date}";

        $times = Cache::remember($cacheKey, 60 * 60 * 24, function () use ($lat, $long) {
            $timestamp = now()->timestamp;
            $response = Http::get("http://api.aladhan.com/v1/timings/{$timestamp}", [
                'latitude' => $lat,
                'longitude' => $long,
                'method' => 20, // Kemenag RI
            ]);

            if ($response->successful()) {
                $data = $response->json()['data']['timings'];

                return [
                    'subuh' => $data['Fajr'],
                    'dzuhur' => $data['Dhuhr'],
                    'ashar' => $data['Asr'],
                    'maghrib' => $data['Maghrib'],
                    'isya' => $data['Isha'],
                ];
            }

            return null;
        });

        if (! $times) {
            return response()->json(['error' => 'Failed to fetch prayer times'], 503);
        }

        return response()->json($times);
    }
}
