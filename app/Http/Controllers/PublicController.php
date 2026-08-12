<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\CommitteeMember;
use App\Models\Slide;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    // --- Helper for Prayer Times ---
    private function getPrayerTimes()
    {
        // Try to get from API or Database, fallback to static
        try {
            // Get location from settings
            $latitude = setting('location_latitude', '-6.200000');
            $longitude = setting('location_longitude', '106.816666');

            $date = now()->format('d-m-Y');
            $response = Http::timeout(3)->get("http://api.aladhan.com/v1/timings/$date", [
                'latitude' => $latitude,
                'longitude' => $longitude,
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

    public function structure(): Response
    {
        $committee = CommitteeMember::where('is_active', true)
            ->whereNotNull('photo_path')
            ->where('photo_path', '!=', '')
            ->orderBy('division')
            ->orderBy('order')
            ->get()
            ->groupBy('division');

        return Inertia::render('Public/Struktur', [
            'committee' => $committee,
        ]);
    }

    public function keuangan(): Response
    {
        // Get latest 50 approved transactions
        $transactions = Transaction::with('verifiedBy', 'approvedBy')
            ->approved()
            ->latest('date')
            ->take(50)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'uuid' => $t->uuid,
                'type' => $t->type,
                'category' => $t->category,
                'amount' => $t->amount,
                'formatted_amount' => $t->formatted_amount,
                'description' => $t->description,
                'date' => $t->date->format('d M Y'),
                'proof_url' => $t->proof_url,
                'verified_by_name' => $t->verifiedBy?->name,
            ]);

        // Financial summary
        $totalIncome = Transaction::income()->approved()->sum('amount');
        $totalExpense = Transaction::expense()->approved()->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return Inertia::render('Public/Keuangan', [
            'transactions' => $transactions,
            'summary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'balance' => $balance,
                'formatted_income' => 'Rp '.number_format($totalIncome, 0, ',', '.'),
                'formatted_expense' => 'Rp '.number_format($totalExpense, 0, ',', '.'),
                'formatted_balance' => 'Rp '.number_format($balance, 0, ',', '.'),
            ],
        ]);
    }

    public function aset(): Response
    {
        $assets = Asset::latest()->get();

        return Inertia::render('Public/Aset', [
            'assets' => $assets,
        ]);
    }

    public function galeri(): Response
    {
        $slides = Slide::where('is_active', true)
            ->orderBy('order')
            ->get();

        return Inertia::render('Public/Galeri', [
            'slides' => $slides,
        ]);
    }

    // --- NEW METHODS ---

    public function berita(): Response
    {
        $posts = \App\Models\Post::published()
            ->with('author')
            ->latest('published_at')
            ->paginate(9)
            ->through(fn ($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'image_url' => $post->image_url,
                'published_at' => $post->published_at->translatedFormat('d F Y'),
                'author_name' => $post->author->name,
            ]);

        return Inertia::render('Public/Berita', [
            'posts' => $posts,
        ]);
    }

    public function post(\App\Models\Post $post): Response
    {
        if (! $post->is_published) {
            abort(404);
        }

        return Inertia::render('Public/Post', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'image_url' => $post->image_url,
                'published_at' => $post->published_at->translatedFormat('l, d F Y'),
                'author_name' => $post->author->name,
                // Add related posts if needed later
            ],
        ]);
    }

    public function tentang(): Response
    {
        $settings = [
            'hero_title' => setting('about_hero_title', 'Tentang Kami'),
            'hero_subtitle' => setting('about_hero_subtitle', 'Sejarah dan visi misi Masjid Al-Hidayah dalam melayani umat.'),
            'vision' => setting('about_vision'),
            'mission' => setting('about_mission'),
            'history' => setting('about_history'),
            'image' => setting('about_image'),
        ];

        return Inertia::render('Public/Tentang', [
            'about' => $settings,
        ]);
    }

    public function jumat(): Response
    {
        // Get next Friday date
        $nextFridayStr = $this->getNextFriday(); // Returns "d F Y" string, but we need date object for query if storing as date...
        // Actually getNextFriday returns formatted string. Let's fix this helper or just find by date logic.

        // Better logic: Find the upcoming Friday Schedule from DB
        // If we want exact match for "this week friday":
        $today = now();
        $targetDate = $today->dayOfWeek == Carbon::FRIDAY ? $today : $today->next(Carbon::FRIDAY);

        $schedule = \App\Models\FridaySchedule::whereDate('date', $targetDate)->first();

        $scheduleData = $schedule ? [
            'date' => $schedule->date->translatedFormat('d F Y'),
            'time' => \Carbon\Carbon::parse($schedule->time)->format('H:i'),
            'khatib' => $schedule->khatib,
            'imam' => $schedule->imam,
            'muadzin' => $schedule->muadzin,
            'bilal' => $schedule->bilal,
            'title' => $schedule->title,
        ] : [
            'date' => $targetDate->translatedFormat('d F Y'),
            'time' => '12:00',
            'khatib' => '-',
            'imam' => '-',
            'muadzin' => '-',
            'bilal' => '-',
        ];

        return Inertia::render('Public/Jumat', [
            'schedule' => $scheduleData,
            'prayerTimes' => $this->getPrayerTimes(),
        ]);
    }

    public function jadwal(): Response
    {
        return Inertia::render('Public/Jadwal', [
            'prayerTimes' => $this->getPrayerTimes(),
        ]);
    }

    public function kiblat(): Response
    {
        return Inertia::render('Public/Kiblat');
    }

    public function agenda(): Response
    {
        // Real active agendas
        $agendas = \App\Models\Agenda::active()
            ->whereDate('date', '>=', now())
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get()
            ->map(function ($agenda) {
                return [
                    'id' => $agenda->id,
                    'title' => $agenda->title,
                    'date' => \Carbon\Carbon::parse($agenda->date)->translatedFormat('l, d F Y'),
                    'time' => \Carbon\Carbon::parse($agenda->time)->format('H:i').' WIB',
                    'location' => $agenda->location,
                    'description' => $agenda->description ?? '-',
                    // 'speaker' is not in DB yet, but could be added later or part of description
                ];
            });

        return Inertia::render('Public/Agenda', [
            'agendas' => $agendas,
        ]);
    }

    public function tarbiyah(): Response
    {
        $classes = [
            [
                'title' => 'Tahsin & Tajwid Al-Quran',
                'category' => 'Al-Quran',
                'description' => 'Program perbaikan bacaan Al-Quran sesuai dengan kaidah tajwid secara tartil, dibimbing langsung oleh ustadz berpengalaman.',
                'schedule' => 'Setiap Sabtu & Ahad (Ba\'da Ashar)',
                'instructor' => 'Ustadz Hamzah Al-Hafizh',
                'level' => 'Semua Tingkatan',
                'badge' => 'Pendaftaran Buka',
                'icon' => '📖',
            ],
            [
                'title' => 'Kajian Riyadhus Shalihin',
                'category' => 'Hadits',
                'description' => 'Membahas bab demi bab dari kitab Riyadhus Shalihin mengenai adab, akhlak, keutamaan amal, dan tata krama islami.',
                'schedule' => 'Setiap Rabu Malam (Ba\'da Maghrib)',
                'instructor' => 'Ustadz Ahmad Fauzi, Lc.',
                'level' => 'Umum',
                'badge' => 'Rutin',
                'icon' => '📚',
            ],
            [
                'title' => 'Halaqah Tafsir Al-Quran',
                'category' => 'Tafsir',
                'description' => 'Memahami makna mendalam dari ayat-ayat pilihan untuk diimplementasikan dalam amalan kehidupan sehari-hari.',
                'schedule' => 'Setiap Jumat Subuh',
                'instructor' => 'Dr. KH. Abdullah Hakim',
                'level' => 'Umum',
                'badge' => 'Rutin',
                'icon' => '🕌',
            ],
            [
                'title' => 'Bahasa Arab Dasar (Al-Muyassar)',
                'category' => 'Bahasa',
                'description' => 'Mempelajari dasar-dasar ilmu nahwu dan sharaf untuk membantu memahami literatur bahasa Arab dan bacaan sholat.',
                'schedule' => 'Setiap Selasa Malam (Ba\'da Isya)',
                'instructor' => 'Ustadz Rahmat Hidayat, B.A.',
                'level' => 'Pemula',
                'badge' => 'Angkatan Baru',
                'icon' => '✍️',
            ],
            [
                'title' => 'Kajian Fiqih Ibadah',
                'category' => 'Fiqih',
                'description' => 'Pembahasan fikih keseharian bersumber dari madzhab Syafi\'i mulai dari thaharah, sholat, puasa, hingga zakat.',
                'schedule' => 'Setiap Ahad Subuh',
                'instructor' => 'KH. Zainal Arifin, M.A.',
                'level' => 'Umum',
                'badge' => 'Rutin',
                'icon' => '⚖️',
            ],
            [
                'title' => 'Tahfidz Al-Quran Dewasa',
                'category' => 'Hafalan',
                'description' => 'Program setoran hafalan mandiri terjadwal dengan sistem mutaba\'ah yang disiplin dan target terukur.',
                'schedule' => 'Setiap Senin & Kamis Malam (Ba\'da Maghrib)',
                'instructor' => 'Ustadz Bilal Al-Fatih',
                'level' => 'Khusus Dewasa',
                'badge' => 'Quota Terbatas',
                'icon' => '🌟',
            ],
        ];

        $lectures = \App\Models\Lecture::where('is_active', true)
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get()
            ->map(function ($lecture) {
                return [
                    'title' => $lecture->title,
                    'speaker' => $lecture->speaker,
                    'date' => \Carbon\Carbon::parse($lecture->date)->translatedFormat('l, d F Y'),
                    'time' => $lecture->time,
                    'location' => $lecture->location,
                    'image' => $lecture->image_url,
                ];
            });

        return Inertia::render('Public/Tarbiyah', [
            'classes' => $classes,
            'lectures' => $lectures,
        ]);
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
