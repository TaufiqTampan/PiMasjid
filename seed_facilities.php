<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Facility;
use Illuminate\Support\Str;

$facilities = [
    [
        'name' => 'Aula Serbaguna Utama Masjid',
        'slug' => Str::slug('Aula Serbaguna Utama Masjid'),
        'facility_type' => 'room',
        'capacity' => 300,
        'description' => 'Ruangan aula serbaguna luas di lantai 2 yang dilengkapi pendingin udara (AC), panggung utama, sound system, serta penerangan memadai untuk acara akad nikah, resepsi syari, seminar, dan pengajian umum.',
        'terms' => 'Penggunaan wajib menjaga ketertiban, tidak merusak fasilitas, dan menjaga kebersihan ruangan setelah digunakan.',
        'image_url' => 'https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=800&q=80',
        'is_active' => true,
    ],
    [
        'name' => 'Mobil Ambulans Operasional Gratis',
        'slug' => Str::slug('Mobil Ambulans Operasional Gratis'),
        'facility_type' => 'vehicle',
        'capacity' => 5,
        'description' => 'Mobil ambulans siaga 24 jam untuk pengantaran jenazah, rujukan orang sakit, atau bantuan darurat medis bagi warga setempat secara gratis.',
        'terms' => 'Melampirkan KTP pemohon dan menginformasikan lokasi penjemputan serta tujuan.',
        'image_url' => 'https://images.unsplash.com/photo-1587745416684-47953f16f02f?auto=format&fit=crop&w=800&q=80',
        'is_active' => true,
    ],
    [
        'name' => 'Set Sound System Portable Wireless',
        'slug' => Str::slug('Set Sound System Portable Wireless'),
        'facility_type' => 'equipment',
        'capacity' => 100,
        'description' => 'Paket sound system portable outdoor yang dilengkapi dengan 2 microphone wireless, speaker portable 15 inch, dan aki cadangan untuk kegiatan outdoor jamaah.',
        'terms' => 'Peminjam bertanggung jawab penuh atas keutuhan dan fungsi perlengkapan.',
        'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
        'is_active' => true,
    ],
    [
        'name' => 'Tenda & Set Karpet Sajadah Portable',
        'slug' => Str::slug('Tenda dan Set Karpet Sajadah Portable'),
        'facility_type' => 'equipment',
        'capacity' => 150,
        'description' => 'Tenda lipat 3x6m dan 10 gulung karpet sajadah tebal untuk kegiatan pengajian outdoor, takziah, atau haatan warga.',
        'terms' => 'Harus dikembalikan dalam keadaan bersih dan kering.',
        'image_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
        'is_active' => true,
    ],
];

foreach ($facilities as $data) {
    Facility::updateOrCreate(
        ['slug' => $data['slug']],
        $data
    );
}

echo "Facilities seeded successfully!\n";
