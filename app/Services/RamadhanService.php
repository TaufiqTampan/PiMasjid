<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RamadhanService
{
    /**
     * Approximate start date for Ramadhan by Hijri year
     */
    protected array $ramadhanStarts = [
        1445 => '2024-03-12',
        1446 => '2025-03-01',
        1447 => '2026-02-18',
        1448 => '2027-02-08',
    ];

    /**
     * Popular Indonesian city presets
     */
    public const CITY_PRESETS = [
        ['name' => 'Jakarta (DKI)', 'lat' => -6.2088, 'lng' => 106.8456, 'timezone' => 7],
        ['name' => 'Bandung (Jabar)', 'lat' => -6.9175, 'lng' => 107.6191, 'timezone' => 7],
        ['name' => 'Surabaya (Jatim)', 'lat' => -7.2575, 'lng' => 112.7521, 'timezone' => 7],
        ['name' => 'Semarang (Jateng)', 'lat' => -6.9667, 'lng' => 110.4167, 'timezone' => 7],
        ['name' => 'Yogyakarta (DIY)', 'lat' => -7.7956, 'lng' => 110.3695, 'timezone' => 7],
        ['name' => 'Medan (Sumut)', 'lat' => 3.5952, 'lng' => 98.6722, 'timezone' => 7],
        ['name' => 'Palembang (Sumsel)', 'lat' => -2.9761, 'lng' => 104.7754, 'timezone' => 7],
        ['name' => 'Makassar (Sulsel)', 'lat' => -5.1477, 'lng' => 119.4327, 'timezone' => 8],
        ['name' => 'Denpasar (Bali)', 'lat' => -8.6705, 'lng' => 115.2126, 'timezone' => 8],
        ['name' => 'Banjarmasin (Kalsel)', 'lat' => -3.3194, 'lng' => 114.5908, 'timezone' => 8],
        ['name' => 'Jayapura (Papua)', 'lat' => -2.5337, 'lng' => 140.7181, 'timezone' => 9],
    ];

    /**
     * Generate 30 days of Ramadhan schedule
     */
    public function getImsakiyahSchedule(float $latitude, float $longitude, string $cityName = 'Jakarta', int $hijriYear = 1446, int $timezone = 7): array
    {
        $startDateStr = $this->ramadhanStarts[$hijriYear] ?? '2025-03-01';
        $startDate = Carbon::parse($startDateStr);

        $cacheKey = "imsakiyah_{$hijriYear}_".round($latitude, 3).'_'.round($longitude, 3);

        return Cache::remember($cacheKey, 60 * 60 * 24 * 7, function () use ($startDate, $latitude, $longitude, $cityName, $hijriYear, $timezone, $startDateStr) {
            $days = [];

            for ($i = 0; $i < 30; $i++) {
                $currentDate = $startDate->copy()->addDays($i);
                $dayNumber = $i + 1;

                $timings = $this->calculateDailyTimings($currentDate, $latitude, $longitude, $timezone);

                // Add 10-day phase name
                $phase = 'Rahmat (10 Hari Pertama)';
                $phaseColor = 'emerald';
                if ($dayNumber > 10 && $dayNumber <= 20) {
                    $phase = 'Maghfirah (10 Hari Kedua)';
                    $phaseColor = 'teal';
                } elseif ($dayNumber > 20) {
                    $phase = 'Itqun Minan Nar (10 Hari Terakhir)';
                    $phaseColor = 'amber';
                }

                $days[] = [
                    'day' => $dayNumber,
                    'hijri_day' => "{$dayNumber} Ramadhan {$hijriYear} H",
                    'date' => $currentDate->format('Y-m-d'),
                    'day_name' => $this->getIndonesianDayName($currentDate->dayOfWeek),
                    'formatted_date' => $this->formatIndonesianDate($currentDate),
                    'phase' => $phase,
                    'phase_color' => $phaseColor,
                    'imsak' => $timings['imsak'],
                    'subuh' => $timings['subuh'],
                    'terbit' => $timings['terbit'],
                    'dhuha' => $timings['dhuha'],
                    'dzuhur' => $timings['dzuhur'],
                    'ashar' => $timings['ashar'],
                    'maghrib' => $timings['maghrib'],
                    'isya' => $timings['isya'],
                ];
            }

            return [
                'hijri_year' => $hijriYear,
                'city' => $cityName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => $timezone,
                'start_date' => $startDateStr,
                'schedule' => $days,
            ];
        });
    }

    /**
     * Astronomical prayer time calculation (Standard Kemenag RI method)
     */
    protected function calculateDailyTimings(Carbon $date, float $lat, float $lng, int $tz = 7): array
    {
        $year = $date->year;

        // Julian date
        $a = floor((14 - $date->month) / 12);
        $y = $year + 4800 - $a;
        $m = $date->month + 12 * $a - 3;
        $julianDate = $date->day + floor((153 * $m + 2) / 5) + 365 * $y + floor($y / 4) - floor($y / 100) + floor($y / 400) - 32045;

        $d = $julianDate - 2451545.0;

        // Mean solar anomaly and longitude
        $g = 357.529 + 0.98560028 * $d;
        $q = 280.459 + 0.98564736 * $d;
        $l = $q + 1.915 * sin(deg2rad($g)) + 0.020 * sin(deg2rad(2 * $g));

        // Sun declination
        $e = 23.439 - 0.00000036 * $d;
        $sinDec = sin(deg2rad($e)) * sin(deg2rad($l));
        $dec = rad2deg(asin($sinDec));

        // Equation of time (in hours)
        $ra = rad2deg(atan2(cos(deg2rad($e)) * sin(deg2rad($l)), cos(deg2rad($l)))) / 15;
        $ra = $this->fixHour($ra);
        $eqt = $q / 15 - $ra;

        // Solar noon (Dzuhur) in hours
        $noon = 12 + $tz - $lng / 15 - $eqt;

        // Kemenag parameters:
        // Fajr (Subuh): Sun altitude = -20 degrees
        // Sunrise (Terbit): Sun altitude = -0.833 degrees (atmospheric refraction + sun disk)
        // Isha (Isya): Sun altitude = -18 degrees
        // Asr: shadow ratio = 1 (Shafi'i/Maliki/Hanbali, standard Indonesia)
        $fajrAngle = -20.0;
        $sunriseAngle = -0.833;
        $ishaAngle = -18.0;

        $fajrT = $this->calculateHourAngle($fajrAngle, $lat, $dec) / 15;
        $sunriseT = $this->calculateHourAngle($sunriseAngle, $lat, $dec) / 15;
        $ishaT = $this->calculateHourAngle($ishaAngle, $lat, $dec) / 15;

        // Asr altitude angle above horizon: arccot(1 + tan(|lat - dec|)) = atan(1 / (1 + tan(|lat - dec|)))
        $asrAltitude = rad2deg(atan(1.0 / (1.0 + tan(deg2rad(abs($lat - $dec))))));
        $asrT = $this->calculateHourAngle($asrAltitude, $lat, $dec) / 15;

        // Maghrib angle (Sunset)
        $sunsetAngle = -0.833;
        $sunsetT = $this->calculateHourAngle($sunsetAngle, $lat, $dec) / 15;

        // Times in decimal hours + 2 minutes ihtiyat (safety precaution standard in Indonesia)
        $ihtiyatMinutes = 2 / 60;

        $subuhHour = $noon - $fajrT + $ihtiyatMinutes;
        $terbitHour = $noon - $sunriseT;
        $dhuhaHour = $terbitHour + 25 / 60; // ~25 mins after sunrise
        $dzuhurHour = $noon + $ihtiyatMinutes;
        $asharHour = $noon + $asrT + $ihtiyatMinutes;
        $maghribHour = $noon + $sunsetT + $ihtiyatMinutes;
        $isyaHour = $noon + $ishaT + $ihtiyatMinutes;

        // Imsak is 10 minutes before Subuh
        $imsakHour = $subuhHour - 10 / 60;

        return [
            'imsak' => $this->decimalToTime($imsakHour),
            'subuh' => $this->decimalToTime($subuhHour),
            'terbit' => $this->decimalToTime($terbitHour),
            'dhuha' => $this->decimalToTime($dhuhaHour),
            'dzuhur' => $this->decimalToTime($dzuhurHour),
            'ashar' => $this->decimalToTime($asharHour),
            'maghrib' => $this->decimalToTime($maghribHour),
            'isya' => $this->decimalToTime($isyaHour),
        ];
    }

    protected function calculateHourAngle(float $angle, float $lat, float $dec): float
    {
        $cosHour = (sin(deg2rad($angle)) - sin(deg2rad($lat)) * sin(deg2rad($dec))) / (cos(deg2rad($lat)) * cos(deg2rad($dec)));

        if ($cosHour > 1) {
            $cosHour = 1;
        } elseif ($cosHour < -1) {
            $cosHour = -1;
        }

        return rad2deg(acos($cosHour));
    }

    protected function fixHour(float $hour): float
    {
        $hour = $hour - 24 * floor($hour / 24);

        return $hour < 0 ? $hour + 24 : $hour;
    }

    protected function decimalToTime(float $decimal): string
    {
        $decimal = $this->fixHour($decimal);
        $hours = floor($decimal);
        $minutes = floor(($decimal - $hours) * 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    protected function getIndonesianDayName(int $dayOfWeek): string
    {
        $days = [
            Carbon::SUNDAY => 'Ahad',
            Carbon::MONDAY => 'Senin',
            Carbon::TUESDAY => 'Selasa',
            Carbon::WEDNESDAY => 'Rabu',
            Carbon::THURSDAY => 'Kamis',
            Carbon::FRIDAY => 'Jumat',
            Carbon::SATURDAY => 'Sabtu',
        ];

        return $days[$dayOfWeek] ?? '';
    }

    protected function formatIndonesianDate(Carbon $date): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $dayName = $this->getIndonesianDayName($date->dayOfWeek);
        $monthName = $months[$date->month] ?? '';

        return "{$dayName}, {$date->day} {$monthName} {$date->year}";
    }
}
