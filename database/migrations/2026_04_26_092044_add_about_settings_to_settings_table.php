<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'about_hero_title',
                'label' => 'Judul Halaman (Hero)',
                'value' => 'Tentang Kami',
                'type' => 'text',
                'group' => 'about',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'about_hero_subtitle',
                'label' => 'Subjudul Halaman (Hero)',
                'value' => 'Sejarah dan visi misi Masjid dalam melayani umat.',
                'type' => 'text',
                'group' => 'about',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'about_vision',
                'label' => 'Visi',
                'value' => 'Menjadi pusat peradaban dan kemakmuran umat.',
                'type' => 'textarea',
                'group' => 'about',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'about_mission',
                'label' => 'Misi',
                'value' => "1. Menyelenggarakan ibadah dengan nyaman dan khusyuk.\n2. Mengadakan kajian keislaman secara rutin.\n3. Memberdayakan ekonomi umat berbasis masjid.",
                'type' => 'textarea',
                'group' => 'about',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'about_history',
                'label' => 'Sejarah Singkat',
                'value' => 'Masjid ini didirikan dengan tujuan menjadi pusat peribadatan dan penyebaran ilmu agama.',
                'type' => 'textarea',
                'group' => 'about',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'about_image',
                'label' => 'Gambar Sejarah/Profil',
                'value' => '',
                'type' => 'image',
                'group' => 'about',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \Illuminate\Support\Facades\DB::table('settings')->insert($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('settings')
            ->where('group', 'about')
            ->delete();
    }
};
