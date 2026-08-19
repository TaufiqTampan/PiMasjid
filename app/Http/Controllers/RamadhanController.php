<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\RamadhanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class RamadhanController extends Controller
{
    public function __construct(
        protected RamadhanService $ramadhanService
    ) {}

    /**
     * Display the interactive Ramadhan calendar & Imsakiyah schedule
     */
    public function index(Request $request): Response
    {
        $defaultLat = (float) setting('location_latitude', -6.2088);
        $defaultLng = (float) setting('location_longitude', 106.8456);
        $defaultCity = setting('location_city', 'Jakarta');

        $lat = (float) $request->input('lat', $defaultLat);
        $lng = (float) $request->input('lng', $defaultLng);
        $city = $request->input('city', $defaultCity);
        $hijriYear = (int) $request->input('year', 1446);
        $timezone = (int) $request->input('tz', 7);

        $scheduleData = $this->ramadhanService->getImsakiyahSchedule($lat, $lng, $city, $hijriYear, $timezone);

        // Find today's Ramadhan info if currently in range
        $todayStr = Carbon::today()->format('Y-m-d');
        $todaySchedule = null;
        $activeDayIndex = -1;

        foreach ($scheduleData['schedule'] as $index => $day) {
            if ($day['date'] === $todayStr) {
                $todaySchedule = $day;
                $activeDayIndex = $index;
                break;
            }
        }

        // If today is before or after Ramadhan, default today's preview to Day 1
        if (! $todaySchedule && count($scheduleData['schedule']) > 0) {
            $todaySchedule = $scheduleData['schedule'][0];
        }

        return Inertia::render('Public/Ramadhan', [
            'scheduleData' => $scheduleData,
            'todaySchedule' => $todaySchedule,
            'activeDayIndex' => $activeDayIndex,
            'currentParams' => [
                'lat' => $lat,
                'lng' => $lng,
                'city' => $city,
                'year' => $hijriYear,
                'tz' => $timezone,
            ],
            'cityPresets' => RamadhanService::CITY_PRESETS,
            'defaultMasjidLocation' => [
                'city' => $defaultCity,
                'lat' => $defaultLat,
                'lng' => $defaultLng,
            ],
        ]);
    }

    /**
     * Download or stream printable PDF of Ramadhan Imsakiyah
     */
    public function exportPdf(Request $request): HttpResponse
    {
        $defaultLat = (float) setting('location_latitude', -6.2088);
        $defaultLng = (float) setting('location_longitude', 106.8456);
        $defaultCity = setting('location_city', 'Jakarta');

        $lat = (float) $request->input('lat', $defaultLat);
        $lng = (float) $request->input('lng', $defaultLng);
        $city = $request->input('city', $defaultCity);
        $hijriYear = (int) $request->input('year', 1446);
        $timezone = (int) $request->input('tz', 7);

        $scheduleData = $this->ramadhanService->getImsakiyahSchedule($lat, $lng, $city, $hijriYear, $timezone);

        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $data = [
            'scheduleData' => $scheduleData,
            'settings' => $settings,
            'year' => $hijriYear,
            'city' => $city,
            'lat' => $lat,
            'lng' => $lng,
            'generated_at' => Carbon::now()->translatedFormat('d F Y H:i'),
        ];

        $cleanCity = preg_replace('/[^A-Za-z0-9_\-]/', '_', $city);
        $fileName = "Jadwal_Imsakiyah_Ramadhan_{$hijriYear}_{$cleanCity}.pdf";

        $pdf = Pdf::loadView('exports.imsakiyah_pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream($fileName);
    }
}
