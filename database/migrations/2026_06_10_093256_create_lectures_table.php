<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lectures', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('speaker');
            $table->date('date');
            $table->string('time');
            $table->string('location');
            $table->string('image_path')->nullable();
            $table->string('cloudinary_public_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default lectures
        \Illuminate\Support\Facades\DB::table('lectures')->insert([
            [
                'title' => 'Urgensi Adab Sebelum Ilmu',
                'speaker' => 'Ustadz Dr. Firanda Andirja, Lc., M.A.',
                'date' => '2026-05-24',
                'time' => '09:00 - 11:30 WIB',
                'location' => 'Ruang Utama Masjid Al-Hidayah',
                'image_path' => 'https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=600',
                'cloudinary_public_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Meneladani Kehidupan Generasi Sahabat',
                'speaker' => 'Ustadz Khalid Basalamah, M.A.',
                'date' => '2026-05-30',
                'time' => '18:30 WIB (Ba\'da Maghrib) - Selesai',
                'location' => 'Masjid Al-Hidayah & Live Streaming',
                'image_path' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?q=80&w=600',
                'cloudinary_public_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lectures');
    }
};
