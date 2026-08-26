<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            [
                'key' => 'site_name',
                'value' => 'MasjidVision',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Nama Masjid',
            ],
            [
                'key' => 'logo_path',
                'value' => null,
                'type' => 'image',
                'group' => 'general',
                'label' => 'Logo Masjid',
            ],
            [
                'key' => 'favicon_path',
                'value' => null,
                'type' => 'image',
                'group' => 'general',
                'label' => 'Favicon Website',
            ],

            // Report Settings
            [
                'key' => 'chairman_name',
                'value' => 'H. Fulan Bin Fulan',
                'type' => 'text',
                'group' => 'report',
                'label' => 'Nama Ketua DKM (Tanda Tangan)',
            ],
            [
                'key' => 'treasurer_name',
                'value' => 'Hj. Fulanah',
                'type' => 'text',
                'group' => 'report',
                'label' => 'Nama Bendahara (Tanda Tangan)',
            ],

            // Hero Section
            [
                'key' => 'hero_title',
                'value' => 'Pusat Ibadah & Kegiatan Umat',
                'type' => 'text',
                'group' => 'hero',
                'label' => 'Judul Hero',
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'Masjid Al-Hidayah, Jl. Contoh No. 123, Jakarta Selatan',
                'type' => 'textarea',
                'group' => 'hero',
                'label' => 'Subjudul / Alamat Singkat',
            ],
            [
                'key' => 'hero_bg_image',
                'value' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?q=80&w=2000&auto=format&fit=crop',
                'type' => 'image',
                'group' => 'hero',
                'label' => 'Background Hero',
            ],

            // Contact
            [
                'key' => 'address',
                'value' => 'Jl. Contoh No. 123, Jakarta Selatan, DKI Jakarta 12345',
                'type' => 'textarea',
                'group' => 'contact',
                'label' => 'Alamat Lengkap',
            ],
            [
                'key' => 'email',
                'value' => 'info@masjidalhidayah.com',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Email',
            ],
            [
                'key' => 'phone',
                'value' => '+62 812 3456 7890',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Nomor Telepon',
            ],
            [
                'key' => 'whatsapp',
                'value' => '6281234567890',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Nomor WhatsApp (format: 62...)',
            ],
            [
                'key' => 'maps_embed_url',
                'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126907.08660340324!2d106.726588!3d-6.284028!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f0322ba7b2c7%3A0x6e6e28ce073c1d0!2sMasjid%20Istiqlal!5e0!3m2!1sid!2sid!4v1705739000000!5m2!1sid!2sid',
                'type' => 'textarea',
                'group' => 'contact',
                'label' => 'Link Google Maps Embed',
            ],

            // Social Media
            [
                'key' => 'facebook_url',
                'value' => '#',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Facebook URL',
            ],
            [
                'key' => 'instagram_url',
                'value' => '#',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Instagram URL',
            ],
            [
                'key' => 'youtube_url',
                'value' => '#',
                'type' => 'text',
                'group' => 'social',
                'label' => 'YouTube URL',
            ],

            // Footer
            [
                'key' => 'footer_text',
                'value' => 'Masjid Al-Hidayah adalah pusat kegiatan ibadah dan sosial kemasyarakatan yang bertujuan membangun peradaban islam yang rahmatan lil alamin.',
                'type' => 'textarea',
                'group' => 'footer',
                'label' => 'Teks Footer',
            ],
            [
                'key' => 'copyright_text',
                'value' => '© 2024 Masjid Al-Hidayah. All rights reserved.',
                'type' => 'text',
                'group' => 'footer',
                'label' => 'Teks Copyright',
            ],

            // Location (for prayer times)
            [
                'key' => 'location_latitude',
                'value' => '-6.200000',
                'type' => 'text',
                'group' => 'location',
                'label' => 'Latitude (Koordinat Lintang)',
            ],
            [
                'key' => 'location_longitude',
                'value' => '106.816666',
                'type' => 'text',
                'group' => 'location',
                'label' => 'Longitude (Koordinat Bujur)',
            ],
            [
                'key' => 'location_city',
                'value' => 'Jakarta',
                'type' => 'text',
                'group' => 'location',
                'label' => 'Kota/Daerah',
            ],
            [
                'key' => 'location_timezone',
                'value' => 'Asia/Jakarta',
                'type' => 'text',
                'group' => 'location',
                'label' => 'Zona Waktu',
            ],

            // Display Signage (TV Display Digital)
            [
                'key' => 'display_running_text',
                'value' => 'Selamat datang di MasjidVision. Mohon matikan atau heningkan HP Anda saat berada di dalam area sholat. Luruskan dan rapatkan shaf.',
                'type' => 'textarea',
                'group' => 'display',
                'label' => 'Teks Running Text (Pengumuman Berjalan)',
            ],
            [
                'key' => 'iqamah_duration_subuh',
                'value' => '10',
                'type' => 'text',
                'group' => 'display',
                'label' => 'Jeda Iqamah Subuh (Menit)',
            ],
            [
                'key' => 'iqamah_duration_dhuhr',
                'value' => '10',
                'type' => 'text',
                'group' => 'display',
                'label' => 'Jeda Iqamah Dzuhur (Menit)',
            ],
            [
                'key' => 'iqamah_duration_asr',
                'value' => '10',
                'type' => 'text',
                'group' => 'display',
                'label' => 'Jeda Iqamah Ashar (Menit)',
            ],
            [
                'key' => 'iqamah_duration_maghrib',
                'value' => '7',
                'type' => 'text',
                'group' => 'display',
                'label' => 'Jeda Iqamah Maghrib (Menit)',
            ],
            [
                'key' => 'iqamah_duration_isha',
                'value' => '10',
                'type' => 'text',
                'group' => 'display',
                'label' => 'Jeda Iqamah Isya (Menit)',
            ],
            [
                'key' => 'sholat_duration',
                'value' => '15',
                'type' => 'text',
                'group' => 'display',
                'label' => 'Durasi Mode Sholat / Layar Blackout (Menit)',
            ],
            [
                'key' => 'sholat_mode_style',
                'value' => 'blackout',
                'type' => 'text',
                'group' => 'display',
                'label' => 'Tipe Layar Mode Sholat (blackout = Gelap Total, shaf = Teks Rapatkan Shaf)',
            ],
            [
                'key' => 'sholat_mode_message',
                'value' => 'LURUSKAN & RAPATKAN SHAF',
                'type' => 'text',
                'group' => 'display',
                'label' => 'Pesan Pengingat Mode Sholat',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
