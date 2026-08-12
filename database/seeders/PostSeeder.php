<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            return;
        }

        $posts = [
            [
                'title' => 'Kegiatan Santunan Anak Yatim',
                'excerpt' => 'Alhamdulillah, telah terlaksana kegiatan santunan anak yatim bulanan masjid.',
                'content' => 'Alhamdulillah, pada hari Jumat lalu telah terlaksana kegiatan santunan anak yatim. Sebanyak 50 anak yatim mendapatkan santunan berupa paket sembako dan uang tunai. Terima kasih kepada para donatur yang telah menyisihkan sebagian rezekinya. Semoga Allah membalas dengan kebaikan yang berlipat ganda.',
                'image_path' => null,
                'author_id' => $user->id,
                'published_at' => now()->subDays(2),
                'is_published' => true,
            ],
            [
                'title' => 'Kajian Rutin Malam Ahad',
                'excerpt' => 'Jangan lewatkan kajian rutin setiap malam Ahad bersama Ustadz Fulan.',
                'content' => 'Hadirilah kajian rutin pembahasan Kitab Riyadhus Shalihin setiap malam Ahad ba\'da maghrib. Kajian akan diisi oleh Ustadz Fulan bin Fulan. Mari ajak keluarga dan sahabat untuk memakmurkan masjid kita tercinta.',
                'image_path' => null,
                'author_id' => $user->id,
                'published_at' => now()->subDays(5),
                'is_published' => true,
            ],
            [
                'title' => 'Renovasi Tempat Wudhu',
                'excerpt' => 'Progress renovasi tempat wudhu wanita sudah mencapai 80%.',
                'content' => 'Kami informasikan bahwa renovasi tempat wudhu wanita saat ini sudah mencapai tahap penyelesaian keramik dinidng dan lantai. Insya Allah dalam 2 minggu ke depan sudah dapat digunakan kembali dengan lebih nyaman. Mohon doanya agar pembangunan berjalan lancar.',
                'image_path' => null,
                'author_id' => $user->id,
                'published_at' => now()->subWeek(),
                'is_published' => true,
            ],
        ];

        foreach ($posts as $post) {
            Post::create($post);
        }
    }
}
