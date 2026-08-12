<?php

namespace Database\Seeders;

use App\Models\Agenda;
use App\Models\PrayerTime;
use App\Models\Qurban;
use App\Models\Slide;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\Zakat;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComprehensiveDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting comprehensive data seeding...');

        // Get users for transaction verification
        $admin = User::where('email', 'dev@masjid.com')->first();
        $bendahara = User::where('email', 'bendahara@masjid.com')->first();
        $ketua = User::where('email', 'ketua@masjid.com')->first();

        // Fallback if users not found (e.g. running standalone)
        if (! $admin) {
            $admin = User::first();
        }
        if (! $bendahara) {
            $bendahara = User::first();
        }

        $this->seedTransactions($admin, $bendahara);
        $this->seedZakat($bendahara);
        $this->seedQurban($bendahara);
        $this->seedAgendas();
        $this->seedPrayerTimes();
        $this->seedSlides();
        $this->seedWishlists();

        $this->command->info('✅ Comprehensive data seeded successfully!');
    }

    private function seedTransactions($admin, $bendahara): void
    {
        $this->command->info('💰 Seeding transactions...');

        $transactions = [
            // Income - Last 6 months
            ['type' => 'income', 'category' => 'Infaq', 'amount' => 200000, 'description' => 'Infaq Jumat pertama', 'date' => '2025-08-01', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Donasi', 'amount' => 5000000, 'description' => 'Donasi dari Haji Ahmad', 'date' => '2025-08-15', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Kotak Jumat', 'amount' => 1500000, 'description' => 'Kotak amal Jumat Agustus minggu ke-3', 'date' => '2025-08-20', 'verified_by' => $admin->id],
            ['type' => 'income', 'category' => 'Zakat Fitrah', 'amount' => 3500000, 'description' => 'Penerimaan zakat fitrah', 'date' => '2025-09-10', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Infaq', 'amount' => 750000, 'description' => 'Infaq jumat kedua September', 'date' => '2025-09-12', 'verified_by' => $admin->id],
            ['type' => 'income', 'category' => 'Qurban', 'amount' => 20000000, 'description' => 'Penerimaan dana qurban sapi', 'date' => '2025-09-25', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Donasi', 'amount' => 10000000, 'description' => 'Donasi renovasi dari warga', 'date' => '2025-10-05', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Kotak Jumat', 'amount' => 1800000, 'description' => 'Kotak amal Jumat Oktober minggu pertama', 'date' => '2025-10-10', 'verified_by' => $admin->id],
            ['type' => 'income', 'category' => 'Infaq', 'amount' => 500000, 'description' => 'Infaq bulanan', 'date' => '2025-10-20', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Donasi', 'amount' => 3000000, 'description' => 'Donasi sound system', 'date' => '2025-11-01', 'verified_by' => $admin->id],
            ['type' => 'income', 'category' => 'Kotak Jumat', 'amount' => 2000000, 'description' => 'Kotak amal Jumat November', 'date' => '2025-11-15', 'verified_by' => $admin->id],
            ['type' => 'income', 'category' => 'Infaq', 'amount' => 850000, 'description' => 'Infaq dari jamaah', 'date' => '2025-11-25', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Donasi', 'amount' => 7500000, 'description' => 'Donasi karpet masjid', 'date' => '2025-12-05', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Kotak Jumat', 'amount' => 2200000, 'description' => 'Kotak amal Jumat Desember', 'date' => '2025-12-13', 'verified_by' => $admin->id],
            ['type' => 'income', 'category' => 'Infaq', 'amount' => 950000, 'description' => 'Infaq akhir tahun', 'date' => '2025-12-28', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Donasi', 'amount' => 15000000, 'description' => 'Donasi dari yayasan', 'date' => '2026-01-03', 'verified_by' => $bendahara->id],
            ['type' => 'income', 'category' => 'Kotak Jumat', 'amount' => 1750000, 'description' => 'Kotak amal Jumat Januari pertama', 'date' => '2026-01-10', 'verified_by' => $admin->id],
            ['type' => 'income', 'category' => 'Infaq', 'amount' => 200000, 'description' => 'Infaq jamaah', 'date' => '2026-01-20', 'verified_by' => $admin->id],
            ['type' => 'income', 'category' => 'Donasi', 'amount' => 3500000, 'description' => 'Donasi perawatan', 'date' => '2026-01-22', 'verified_by' => $bendahara->id],

            // Expenses - ALL MUST HAVE proof_image_path
            ['type' => 'expense', 'category' => 'Utilitas', 'amount' => 850000, 'description' => 'Listrik dan air Agustus', 'date' => '2025-08-05', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Operasional', 'amount' => 500000, 'description' => 'Kebersihan dan pemeliharaan', 'date' => '2025-08-12', 'verified_by' => $admin->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Konsumsi', 'amount' => 750000, 'description' => 'Konsumsi kajian rutin', 'date' => '2025-09-01', 'verified_by' => $admin->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Utilitas', 'amount' => 920000, 'description' => 'Listrik dan air September', 'date' => '2025-09-05', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Renovasi', 'amount' => 3500000000, 'description' => 'Renovasi ruang wudhu', 'date' => '2025-10-10', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/renovasi.jpg'],
            ['type' => 'expense', 'category' => 'Operasional', 'amount' => 650000, 'description' => 'Perlengkapan ibadah', 'date' => '2025-10-20', 'verified_by' => $admin->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Utilitas', 'amount' => 880000, 'description' => 'Listrik dan air Oktober', 'date' => '2025-10-05', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Konsumsi', 'amount' => 1200000, 'description' => 'Konsumsi pengajian akbar', 'date' => '2025-11-08', 'verified_by' => $admin->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Operasional', 'amount' => 450000, 'description' => 'ATK dan administrasi', 'date' => '2025-11-15', 'verified_by' => $admin->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Utilitas', 'amount' => 950000, 'description' => 'Listrik dan air November', 'date' => '2025-11-05', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Renovasi', 'amount' => 5500000, 'description' => 'Perbaikan sound system', 'date' => '2025-12-10', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Utilitas', 'amount' => 1100000, 'description' => 'Listrik dan air Desember', 'date' => '2025-12-05', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Konsumsi', 'amount' => 850000, 'description' => 'Konsumsi tahun baru hijriah', 'date' => '2026-01-07', 'verified_by' => $admin->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Operasional', 'amount' => 600000, 'description' => 'Honorarium marbot', 'date' => '2026-01-15', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/default.jpg'],
            ['type' => 'expense', 'category' => 'Utilitas', 'amount' => 980000, 'description' => 'Listrik dan air Januari', 'date' => '2026-01-05', 'verified_by' => $bendahara->id, 'proof_image_path' => 'transactions/default.jpg'],
        ];

        foreach ($transactions as $data) {
            Transaction::create($data);
        }

        $this->command->info('   ✓ Created '.count($transactions).' transactions');
    }

    private function seedZakat($verifier): void
    {
        $this->command->info('📿 Seeding zakat data...');

        $zakats = [
            // Fields: muzakki_name, muzakki_phone, type, amount, payment_type, person_count, year, date, collected_by
            ['muzakki_name' => 'Ahmad Fahrezi', 'muzakki_phone' => '081234567890', 'type' => 'fitrah', 'amount' => 35000, 'payment_type' => 'uang', 'person_count' => 1, 'year' => 1446, 'date' => '2025-09-08', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Budi Santoso', 'muzakki_phone' => '081234567891', 'type' => 'fitrah', 'amount' => 140000, 'payment_type' => 'uang', 'person_count' => 4, 'year' => 1446, 'date' => '2025-09-08', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Siti Nurhaliza', 'muzakki_phone' => '081234567892', 'type' => 'mal', 'amount' => 500000, 'payment_type' => 'uang', 'person_count' => null, 'year' => 1446, 'date' => '2025-09-10', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Haji Abdullah', 'muzakki_phone' => '081234567893', 'type' => 'fitrah', 'amount' => 210000, 'payment_type' => 'uang', 'person_count' => 6, 'year' => 1446, 'date' => '2025-09-09', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Dewi Lestari', 'muzakki_phone' => '081234567894', 'type' => 'fitrah', 'amount' => 35000, 'payment_type' => 'uang', 'person_count' => 1, 'year' => 1446, 'date' => '2025-09-08', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Pak Hendra', 'muzakki_phone' => '081234567895', 'type' => 'fitrah', 'amount' => 105000, 'payment_type' => 'uang', 'person_count' => 3, 'year' => 1446, 'date' => '2025-09-10', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Ibu Fatimah', 'muzakki_phone' => '081234567896', 'type' => 'mal', 'amount' => 750000, 'payment_type' => 'uang', 'person_count' => null, 'year' => 1446, 'date' => '2025-09-11', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Keluarga Wijaya', 'muzakki_phone' => '081234567897', 'type' => 'fitrah', 'amount' => 175000, 'payment_type' => 'uang', 'person_count' => 5, 'year' => 1446, 'date' => '2025-09-09', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Pak Darmawan', 'muzakki_phone' => '081234567898', 'type' => 'mal', 'amount' => 1000000, 'payment_type' => 'uang', 'person_count' => null, 'year' => 1446, 'date' => '2025-09-13', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Ibu Aminah', 'muzakki_phone' => '081234567899', 'type' => 'fitrah', 'amount' => 70000, 'payment_type' => 'uang', 'person_count' => 2, 'year' => 1446, 'date' => '2025-09-08', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Keluarga Hakim', 'muzakki_phone' => '081234567800', 'type' => 'fitrah', 'amount' => 140000, 'payment_type' => 'uang', 'person_count' => 4, 'year' => 1446, 'date' => '2025-09-10', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Dr. Rahmat', 'muzakki_phone' => '081234567801', 'type' => 'mal', 'amount' => 2000000, 'payment_type' => 'uang', 'person_count' => null, 'year' => 1446, 'date' => '2025-09-12', 'collected_by' => $verifier->id],
            ['muzakki_name' => 'Ibu Khadijah', 'muzakki_phone' => '081234567802', 'type' => 'fitrah', 'amount' => 105000, 'payment_type' => 'uang', 'person_count' => 3, 'year' => 1446, 'date' => '2025-09-11', 'collected_by' => $verifier->id],
        ];

        foreach ($zakats as $data) {
            Zakat::create($data);
        }

        $this->command->info('   ✓ Created '.count($zakats).' zakat records');
    }

    private function seedQurban($verifier): void
    {
        $this->command->info('🐐 Seeding qurban data...');

        $qurbans = [
            // Fields: participant_name, participant_phone, animal_type, animal_price, is_shared, status, year, registration_date, registered_by
            ['participant_name' => 'Haji Ahmad', 'participant_phone' => '081212341234', 'animal_type' => 'sapi', 'animal_price' => 21000000, 'is_shared' => false, 'status' => 'paid', 'year' => 1446, 'registration_date' => '2025-08-15', 'registered_by' => $verifier->id],
            ['participant_name' => 'Pak Budi', 'participant_phone' => '081223452345', 'animal_type' => 'kambing', 'animal_price' => 3000000, 'is_shared' => false, 'status' => 'paid', 'year' => 1446, 'registration_date' => '2025-08-16', 'registered_by' => $verifier->id],
            ['participant_name' => 'Ibu Siti', 'participant_phone' => '081234563456', 'animal_type' => 'sapi', 'animal_price' => 3000000, 'is_shared' => true, 'share_count' => 7, 'status' => 'paid', 'year' => 1446, 'registration_date' => '2025-08-17', 'registered_by' => $verifier->id],
            ['participant_name' => 'Keluarga Rahman', 'participant_phone' => '081245674567', 'animal_type' => 'kambing', 'animal_price' => 2500000, 'is_shared' => false, 'status' => 'registered', 'year' => 1446, 'registration_date' => '2025-08-18', 'registered_by' => $verifier->id],
            ['participant_name' => 'Dr. Hendra', 'participant_phone' => '081256785678', 'animal_type' => 'sapi', 'animal_price' => 22000000, 'is_shared' => false, 'status' => 'paid', 'year' => 1446, 'registration_date' => '2025-08-19', 'registered_by' => $verifier->id],
            ['participant_name' => 'Ibu Dewi', 'participant_phone' => '081267896789', 'animal_type' => 'sapi', 'animal_price' => 3000000, 'is_shared' => true, 'share_count' => 7, 'status' => 'paid', 'year' => 1446, 'registration_date' => '2025-08-20', 'registered_by' => $verifier->id],
            ['participant_name' => 'Pak Yusuf', 'participant_phone' => '081278907890', 'animal_type' => 'kambing', 'animal_price' => 3500000, 'is_shared' => false, 'status' => 'registered', 'year' => 1446, 'registration_date' => '2025-08-21', 'registered_by' => $verifier->id],
            ['participant_name' => 'Haji Ismail', 'participant_phone' => '081289018901', 'animal_type' => 'sapi', 'animal_price' => 23000000, 'is_shared' => false, 'status' => 'paid', 'year' => 1446, 'registration_date' => '2025-08-22', 'registered_by' => $verifier->id],
            ['participant_name' => 'Ibu Fatimah', 'participant_phone' => '081290129012', 'animal_type' => 'kambing', 'animal_price' => 2800000, 'is_shared' => false, 'status' => 'paid', 'year' => 1446, 'registration_date' => '2025-08-23', 'registered_by' => $verifier->id],
            ['participant_name' => 'Keluarga Hakim', 'participant_phone' => '081201230123', 'animal_type' => 'sapi', 'animal_price' => 21500000, 'is_shared' => false, 'status' => 'paid', 'year' => 1446, 'registration_date' => '2025-08-24', 'registered_by' => $verifier->id],
        ];

        foreach ($qurbans as $data) {
            Qurban::create($data);
        }

        $this->command->info('   ✓ Created '.count($qurbans).' qurban records');
    }

    private function seedAgendas(): void
    {
        $this->command->info('📅 Seeding agendas...');

        $agendas = [
            // Upcoming events
            ['title' => 'Kajian Subuh Rutin', 'slug' => Str::slug('Kajian Subuh Rutin').'-'.Str::random(6), 'description' => 'Kajian subuh setiap hari dengan tema tafsir Al-Quran', 'date' => Carbon::now()->addDays(1)->format('Y-m-d'), 'time' => '05:00', 'location' => 'Masjid Utama', 'is_active' => true],
            ['title' => 'Pengajian Ahad Pagi', 'slug' => Str::slug('Pengajian Ahad Pagi').'-'.Str::random(6), 'description' => 'Pengajian rutin setiap hari Ahad pagi', 'date' => Carbon::now()->next('Sunday')->format('Y-m-d'), 'time' => '07:00', 'location' => 'Aula Masjid', 'is_active' => true],
            ['title' => 'Kajian Rabu Malam', 'slug' => Str::slug('Kajian Rabu Malam').'-'.Str::random(6), 'description' => 'Kajian rutin malam Rabu dengan Ustadz Ahmad', 'date' => Carbon::now()->next('Wednesday')->format('Y-m-d'), 'time' => '19:30', 'location' => 'Masjid Utama', 'is_active' => true],
            ['title' => 'Tarawih Ramadhan', 'slug' => Str::slug('Tarawih Ramadhan').'-'.Str::random(6), 'description' => 'Sholat tarawih berjamaah setiap malam di bulan Ramadhan', 'date' => '2026-02-15', 'time' => '20:00', 'location' => 'Masjid Utama', 'is_active' => true],
            ['title' => 'Buka Puasa Bersama', 'slug' => Str::slug('Buka Puasa Bersama').'-'.Str::random(6), 'description' => 'Buka puasa bersama jamaah dan anak yatim', 'date' => '2026-02-20', 'time' => '18:00', 'location' => 'Halaman Masjid', 'is_active' => true],
            ['title' => 'Kelas Tahsin Al-Quran', 'slug' => Str::slug('Kelas Tahsin Al-Quran').'-'.Str::random(6), 'description' => 'Kelas perbaikan bacaan Al-Quran untuk jamaah', 'date' => Carbon::now()->addDays(5)->format('Y-m-d'), 'time' => '16:00', 'location' => 'Ruang Kelas', 'is_active' => true],
            ['title' => 'Halaqah Hafalan Juz Amma', 'slug' => Str::slug('Halaqah Hafalan Juz Amma').'-'.Str::random(6), 'description' => 'Program tahfidz juz amma untuk anak-anak', 'date' => Carbon::now()->addDays(3)->format('Y-m-d'), 'time' => '15:30', 'location' => 'Aula Masjid', 'is_active' => true],

            // Past events
            ['title' => 'Peringatan Maulid Nabi', 'slug' => Str::slug('Peringatan Maulid Nabi').'-'.Str::random(6), 'description' => 'Peringatan maulid Nabi Muhammad SAW', 'date' => '2025-12-15', 'time' => '19:00', 'location' => 'Masjid Utama', 'is_active' => false],
            ['title' => 'Santunan Anak Yatim', 'slug' => Str::slug('Santunan Anak Yatim').'-'.Str::random(6), 'description' => 'Pemberian santunan kepada anak yatim', 'date' => '2025-11-20', 'time' => '09:00', 'location' => 'Aula Masjid', 'is_active' => false],
            ['title' => 'Bakti Sosial Ramadhan', 'slug' => Str::slug('Bakti Sosial Ramadhan').'-'.Str::random(6), 'description' => 'Pembagian sembako untuk warga kurang mampu', 'date' => '2025-09-25', 'time' => '10:00', 'location' => 'Halaman Masjid', 'is_active' => false],
        ];

        foreach ($agendas as $data) {
            Agenda::create($data);
        }

        $this->command->info('   ✓ Created '.count($agendas).' agenda items');
    }

    private function seedPrayerTimes(): void
    {
        $this->command->info('🕌 Seeding prayer times...');

        $hijriMonths = ['Muharram', 'Safar', 'Rabiul Awal', 'Rabiul Akhir', 'Jumadil Awal', 'Jumadil Akhir',
            'Rajab', 'Syaban', 'Ramadhan', 'Syawal', 'Dzulqaidah', 'Dzulhijjah'];

        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->addDays($i);
            $hijriDay = 20 + $i;
            $hijriMonth = $hijriMonths[6]; // Rajab

            PrayerTime::create([
                'date' => $date->format('Y-m-d'),
                'hijri_date' => "$hijriDay $hijriMonth 1448",
                'subuh' => '04:'.str_pad(30 + ($i % 5), 2, '0', STR_PAD_LEFT),
                'sunrise' => '05:'.str_pad(45 + ($i % 5), 2, '0', STR_PAD_LEFT),
                'dhuhr' => '12:'.str_pad($i % 10, 2, '0', STR_PAD_LEFT),
                'asr' => '15:'.str_pad(15 + ($i % 10), 2, '0', STR_PAD_LEFT),
                'maghrib' => '18:'.str_pad(20 + ($i % 10), 2, '0', STR_PAD_LEFT),
                'isha' => '19:'.str_pad(30 + ($i % 10), 2, '0', STR_PAD_LEFT),
            ]);
        }

        $this->command->info('   ✓ Created 30 days of prayer times');
    }

    private function seedSlides(): void
    {
        $this->command->info('📺 Seeding TV display slides...');

        $slides = [
            ['title' => 'Jadwal Sholat Hari Ini', 'content' => 'Mari sholat berjamaah tepat waktu', 'image_path' => null, 'is_active' => true, 'order' => 1],
            ['title' => 'Transparansi Keuangan', 'content' => 'Laporan keuangan masjid dapat diakses melalui website', 'image_path' => null, 'is_active' => true, 'order' => 2],
            ['title' => 'Kajian Rutin', 'content' => 'Setiap Rabu malam pukul 19:30 WIB bersama Ustadz Ahmad', 'image_path' => null, 'is_active' => true, 'order' => 3],
            ['title' => 'Infaq Jumat', 'content' => 'Silakan berinfaq setiap hari Jumat untuk operasional masjid', 'image_path' => null, 'is_active' => true, 'order' => 4],
            ['title' => 'Program Tahfidz', 'content' => 'Daftarkan putra-putri Anda di program tahfidz Juz Amma', 'image_path' => null, 'is_active' => true, 'order' => 5],
            ['title' => 'Donasi Qurban', 'content' => 'Mari berqurban untuk kebaikan bersama', 'image_path' => null, 'is_active' => false, 'order' => 6],
            ['title' => 'Kelas Tahsin', 'content' => 'Perbaiki bacaan Al-Quran Anda setiap hari Jumat jam 16:00', 'image_path' => null, 'is_active' => true, 'order' => 7],
            ['title' => 'Website Masjid', 'content' => 'Kunjungi website kami untuk informasi lengkap kegiatan masjid', 'image_path' => null, 'is_active' => true, 'order' => 8],
        ];

        foreach ($slides as $data) {
            Slide::create($data);
        }

        $this->command->info('   ✓ Created '.count($slides).' TV slides');
    }

    private function seedWishlists(): void
    {
        $this->command->info('🎁 Seeding wishlists...');

        $wishlists = [
            ['item_name' => 'Karpet Masjid', 'target_qty' => 50, 'fulfilled_qty' => 32, 'unit_price' => 250000, 'status' => 'active', 'description' => 'Karpet untuk ruang sholat utama ukuran 120x600 cm'],
            ['item_name' => 'Al-Quran Terjemahan', 'target_qty' => 100, 'fulfilled_qty' => 100, 'unit_price' => 150000, 'status' => 'completed', 'description' => 'Al-Quran terjemahan untuk jamaah'],
            ['item_name' => 'Kipas Angin Berdiri', 'target_qty' => 10, 'fulfilled_qty' => 0, 'unit_price' => 800000, 'status' => 'pending', 'description' => 'Kipas angin untuk ruang sholat'],
            ['item_name' => 'Sound System', 'target_qty' => 1, 'fulfilled_qty' => 0, 'unit_price' => 15000000, 'status' => 'active', 'description' => 'Sound system untuk adzan dan pengajian'],
            ['item_name' => 'Rak Sepatu', 'target_qty' => 20, 'fulfilled_qty' => 15, 'unit_price' => 350000, 'status' => 'active', 'description' => 'Rak sepatu bertingkat untuk jamaah'],
            ['item_name' => 'Lampu LED', 'target_qty' => 30, 'fulfilled_qty' => 30, 'unit_price' => 120000, 'status' => 'completed', 'description' => 'Lampu LED hemat energi'],
        ];

        foreach ($wishlists as $data) {
            Wishlist::create($data);
        }

        $this->command->info('   ✓ Created '.count($wishlists).' wishlist items');
    }
}
