<?php

namespace Database\Seeders;

use App\Models\PrayerTime;
use App\Models\Slide;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user for transaction verification
        $user = User::factory()->create([
            'name' => 'Admin Masjid',
            'email' => 'admin@masjid.test',
        ]);

        // Seed Prayer Times
        $this->seedPrayerTimes();

        // Seed Transactions
        $this->seedTransactions($user);

        // Seed Slides
        $this->seedSlides();

        // Seed Wishlists
        $this->seedWishlists();

        $this->command->info('✅ Test data seeded successfully!');
        $this->testModelFeatures($user);
    }

    private function seedPrayerTimes(): void
    {
        $prayerTimes = [
            [
                'date' => '2026-01-20',
                'hijri_date' => '20 Rajab 1448',
                'subuh' => '04:30',
                'sunrise' => '05:45',
                'dhuhr' => '12:00',
                'asr' => '15:15',
                'maghrib' => '18:20',
                'isha' => '19:30',
            ],
            [
                'date' => '2026-01-21',
                'hijri_date' => '21 Rajab 1448',
                'subuh' => '04:31',
                'sunrise' => '05:46',
                'dhuhr' => '12:01',
                'asr' => '15:16',
                'maghrib' => '18:21',
                'isha' => '19:31',
            ],
        ];

        foreach ($prayerTimes as $data) {
            PrayerTime::create($data);
        }

        $this->command->info('Prayer times seeded.');
    }

    private function seedTransactions(User $user): void
    {
        // Income transactions
        Transaction::create([
            'type' => 'income',
            'category' => 'Kotak Jumat',
            'amount' => 1500000,
            'description' => 'Donasi Jumat minggu pertama',
            'verified_by' => $user->id,
            'date' => '2026-01-17',
        ]);

        Transaction::create([
            'type' => 'income',
            'category' => 'Infaq',
            'amount' => 500000,
            'description' => 'Infaq dari jamaah',
            'verified_by' => $user->id,
            'date' => '2026-01-18',
        ]);

        // Expense transaction (with proof)
        Transaction::create([
            'type' => 'expense',
            'category' => 'Operasional',
            'amount' => 750000,
            'description' => 'Pembayaran listrik dan air',
            'proof_image_path' => 'transactions/proof-sample.jpg',
            'verified_by' => $user->id,
            'date' => '2026-01-19',
        ]);

        $this->command->info('Transactions seeded.');
    }

    private function seedSlides(): void
    {
        $slides = [
            [
                'title' => 'Jadwal Sholat Hari Ini',
                'content' => 'Mari sholat berjamaah tepat waktu',
                'image_path' => 'slides/prayer-schedule.jpg',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Program Kajian Rutin',
                'content' => 'Setiap Rabu malam pukul 19:30 WIB',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'title' => 'Laporan Keuangan Bulanan',
                'content' => 'Transparansi keuangan masjid',
                'is_active' => false,
                'order' => 3,
            ],
        ];

        foreach ($slides as $data) {
            Slide::create($data);
        }

        $this->command->info('Slides seeded.');
    }

    private function seedWishlists(): void
    {
        $wishlists = [
            [
                'item_name' => 'Karpet Masjid',
                'target_qty' => 50,
                'fulfilled_qty' => 20,
                'unit_price' => 250000,
                'status' => 'active',
                'description' => 'Karpet untuk ruang sholat utama',
            ],
            [
                'item_name' => 'Al-Quran',
                'target_qty' => 100,
                'fulfilled_qty' => 100,
                'unit_price' => 150000,
                'status' => 'completed',
                'description' => 'Al-Quran terjemahan untuk jamaah',
            ],
            [
                'item_name' => 'Kipas Angin',
                'target_qty' => 10,
                'fulfilled_qty' => 0,
                'unit_price' => 800000,
                'status' => 'pending',
                'description' => 'Kipas angin untuk ruang sholat',
            ],
        ];

        foreach ($wishlists as $data) {
            Wishlist::create($data);
        }

        $this->command->info('Wishlists seeded.');
    }

    private function testModelFeatures(User $user): void
    {
        $this->command->newLine();
        $this->command->info('🧪 Testing Model Features:');
        $this->command->newLine();

        // Test PrayerTime model
        $this->command->info('📿 PrayerTime Model:');
        $prayerTime = PrayerTime::first();
        $this->command->line("  - Formatted Date: {$prayerTime->formatted_date}");
        $this->command->line("  - Formatted Subuh: {$prayerTime->formatted_subuh}");
        $this->command->line('  - Scope Today Count: '.PrayerTime::today()->count());

        // Test Transaction model
        $this->command->newLine();
        $this->command->info('💰 Transaction Model:');
        $transaction = Transaction::first();
        $this->command->line("  - UUID: {$transaction->uuid}");
        $this->command->line("  - Formatted Amount: {$transaction->formatted_amount}");
        $this->command->line("  - Status Label: {$transaction->status_label}");
        $this->command->line('  - Income Count: '.Transaction::income()->count());
        $this->command->line('  - Expense Count: '.Transaction::expense()->count());

        // Test Slide model
        $this->command->newLine();
        $this->command->info('🖼️ Slide Model:');
        $slide = Slide::first();
        $this->command->line("  - Status Label: {$slide->status_label}");
        $this->command->line('  - Active Slides: '.Slide::active()->count());

        // Test Wishlist model
        $this->command->newLine();
        $this->command->info('🎁 Wishlist Model:');
        $wishlist = Wishlist::first();
        $this->command->line("  - Formatted Unit Price: {$wishlist->formatted_unit_price}");
        $this->command->line("  - Total Target: {$wishlist->formatted_total_target}");
        $this->command->line("  - Progress: {$wishlist->progress_percentage}%");
        $this->command->line("  - Remaining Qty: {$wishlist->remaining_qty}");
        $this->command->line('  - Active Count: '.Wishlist::active()->count());
        $this->command->line('  - Completed Count: '.Wishlist::completed()->count());

        $this->command->newLine();
        $this->command->info('✅ All model features working correctly!');
    }
}
