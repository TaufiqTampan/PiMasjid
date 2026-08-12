<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use App\Models\Transaction;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response
    {
        $now = now();

        // Prayer Times (Robust)
        $prayerData = $this->getPrayerTimes();
        $nextPrayer = $this->getNextPrayer($prayerData, $now);

        // Financial Summary (approved only)
        $totalIncome = Transaction::income()->approved()->sum('amount');
        $totalExpense = Transaction::expense()->approved()->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Active Slides
        $activeSlides = Slide::where('is_active', true)
            ->orderBy('order')
            ->take(5)
            ->get()
            ->map(fn ($slide) => [
                'id' => $slide->id,
                'title' => $slide->title,
                'image_url' => $slide->image_url,
            ]);

        // Committee Members (Grouped)
        $committee = \App\Models\CommitteeMember::where('is_active', true)
            ->whereNotNull('photo_path')
            ->where('photo_path', '!=', '')
            ->orderBy('division')
            ->orderBy('order')
            ->get()
            ->groupBy('division')
            ->map(fn ($group) => $group->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'position' => $member->position,
                'division' => $member->division,
                'photo_url' => $member->photo_url,
            ]));

        return Inertia::render('Welcome', [
            'prayerTimes' => $prayerData,
            'nextPrayer' => $nextPrayer,
            'financialSummary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'balance' => $balance,
                'formatted_income' => 'Rp '.number_format($totalIncome, 0, ',', '.'),
                'formatted_expense' => 'Rp '.number_format($totalExpense, 0, ',', '.'),
                'formatted_balance' => 'Rp '.number_format($balance, 0, ',', '.'),
            ],
            'slides' => $activeSlides,
            'committee' => $committee,
            'jumatSchedule' => [
                'khatib' => 'Ustadz Ahmad Dahlan',
                'imam' => 'Ustadz Muhammad Ridwan',
                'date' => $this->getNextFriday(),
            ],
            'posts' => \App\Models\Post::published()
                ->with('author')
                ->latest('published_at')
                ->take(3)
                ->get()
                ->map(fn ($post) => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt,
                    'image_url' => $post->image_url,
                    'published_at' => $post->published_at->translatedFormat('d F Y'),
                    'author_name' => $post->author->name,
                ]),
        ]);
    }

    private function getPrayerTimes()
    {
        // Try to get from API or Database, fallback to static
        try {
            // Using Aladhan API for Jakarta as example source
            $date = now()->format('d-m-Y');
            $response = \Illuminate\Support\Facades\Http::timeout(3)->get("http://api.aladhan.com/v1/timings/$date", [
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'method' => 11, // Majlis Ugama Islam Singapura, close to Indonesia standard
            ]);

            if ($response->successful()) {
                $timings = $response->json('data.timings');

                return [
                    'Subuh' => $timings['Fajr'],
                    'Dzuhur' => $timings['Dhuhr'],
                    'Ashar' => $timings['Asr'],
                    'Maghrib' => $timings['Maghrib'],
                    'Isya' => $timings['Isha'],
                    'date' => now()->translatedFormat('l, d F Y'),
                ];
            }
        } catch (\Exception $e) {
            // Fallback logic below
        }

        // STATIC FALLBACK (If API fails) to prevent "Calculating..." loop
        return [
            'Subuh' => '04:30',
            'Dzuhur' => '12:05',
            'Ashar' => '15:20',
            'Maghrib' => '18:10',
            'Isya' => '19:25',
            'date' => now()->translatedFormat('l, d F Y'),
        ];
    }

    private function getNextPrayer($prayerTimes, $now)
    {
        if (! $prayerTimes) {
            return null;
        } // Should not happen with fallback

        $prayers = [
            'Subuh' => $prayerTimes['Subuh'],
            'Dzuhur' => $prayerTimes['Dzuhur'],
            'Ashar' => $prayerTimes['Ashar'],
            'Maghrib' => $prayerTimes['Maghrib'],
            'Isya' => $prayerTimes['Isya'],
        ];

        foreach ($prayers as $name => $time) {
            // Need to parse time "HH:mm" to Carbon today
            $prayerDateTime = Carbon::createFromFormat('H:i', $time);
            if ($now->lt($prayerDateTime)) {
                return [
                    'name' => $name,
                    'time' => $time,
                    'countdown' => $prayerDateTime->diffForHumans($now, true),
                ];
            }
        }

        // If passed Isya', show tomorrow's Subuh (Generic logic for now, or just null)
        return null;
    }

    private function getNextFriday()
    {
        $now = now();
        if ($now->dayOfWeek === Carbon::FRIDAY) {
            return $now->translatedFormat('d F Y');
        }

        return $now->next(Carbon::FRIDAY)->translatedFormat('d F Y');
    }
}
