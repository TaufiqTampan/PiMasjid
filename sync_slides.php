<?php

use App\Models\Slide;

$slides = [
    [
        'title' => 'Jadwal Sholat Hari Ini',
        'image_path' => 'https://res.cloudinary.com/dkf6fhi4m/image/upload/v1769527547/pimasjid/slides_v2/zfymno5txgsuccirb3x6.jpg',
        'cloudinary_public_id' => 'pimasjid/slides_v2/zfymno5txgsuccirb3x6',
        'order' => 6,
    ],
    [
        'title' => 'Transparansi Keuangan',
        'image_path' => 'https://res.cloudinary.com/dkf6fhi4m/image/upload/v1769527552/pimasjid/slides_v2/uv8j9qcvkddlpgngz0hl.jpg',
        'cloudinary_public_id' => 'pimasjid/slides_v2/uv8j9qcvkddlpgngz0hl',
        'order' => 7,
    ],
    [
        'title' => 'Kajian Rutin',
        'image_path' => 'https://res.cloudinary.com/dkf6fhi4m/image/upload/v1769527555/pimasjid/slides_v2/lkurg70rgtswrtwpxevv.jpg',
        'cloudinary_public_id' => 'pimasjid/slides_v2/lkurg70rgtswrtwpxevv',
        'order' => 8,
    ],
    [
        'title' => 'Program Tahfidz',
        'image_path' => 'https://res.cloudinary.com/dkf6fhi4m/image/upload/v1769527559/pimasjid/slides_v2/di8vu32jmufmapm4xkdj.jpg',
        'cloudinary_public_id' => 'pimasjid/slides_v2/di8vu32jmufmapm4xkdj',
        'order' => 9,
    ],
];

foreach ($slides as $slideData) {
    // Check by title or public ID to avoid duplicates
    $exists = Slide::where('title', $slideData['title'])
        ->orWhere('cloudinary_public_id', $slideData['cloudinary_public_id'])
        ->exists();

    if ($exists) {
        echo "Skipping/Updating: {$slideData['title']} (Already exists or matching Public ID)\n";
        // Optional: Update if exists but url is different
        $slide = Slide::where('title', $slideData['title'])->first();
        if ($slide) {
            $slide->update([
                'image_path' => $slideData['image_path'],
                'cloudinary_public_id' => $slideData['cloudinary_public_id'],
            ]);
        }

        continue;
    }

    echo "Adding: {$slideData['title']}...\n";

    Slide::create([
        'title' => $slideData['title'],
        'image_path' => $slideData['image_path'],
        'cloudinary_public_id' => $slideData['cloudinary_public_id'],
        'order' => $slideData['order'],
        'is_active' => true,
    ]);

    echo "Successfully added: {$slideData['title']}\n";
}

echo "Sync completed!\n";
