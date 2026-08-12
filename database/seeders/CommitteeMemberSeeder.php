<?php

namespace Database\Seeders;

use App\Models\CommitteeMember;
use Illuminate\Database\Seeder;

class CommitteeMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            // Core Leadership (Inti)
            [
                'name' => 'H. Ahmad Dahlan',
                'position' => 'Ketua DKM',
                'division' => 'Inti',
                'photo_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=200&auto=format&fit=crop',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Muhammad Ridwan, S.Pd',
                'position' => 'Sekretaris',
                'division' => 'Inti',
                'photo_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=200&auto=format&fit=crop',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso, S.E',
                'position' => 'Bendahara',
                'division' => 'Inti',
                'photo_path' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=200&auto=format&fit=crop',
                'order' => 3,
                'is_active' => true,
            ],

            // Divisions (Seksi)
            [
                'name' => 'Umar Bakri',
                'position' => 'Kepala Seksi Ibadah',
                'division' => 'Ibadah',
                'photo_path' => 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?q=80&w=200&auto=format&fit=crop',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Ali Rahman',
                'position' => 'Kepala Seksi Pembangunan',
                'division' => 'Pembangunan',
                'photo_path' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=200&auto=format&fit=crop',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Hasan Abdullah',
                'position' => 'Kepala Seksi Pendidikan',
                'division' => 'Pendidikan',
                'photo_path' => 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?q=80&w=200&auto=format&fit=crop',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Ibrahim Malik',
                'position' => 'Kepala Seksi Sosial',
                'division' => 'Sosial',
                'photo_path' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=200&auto=format&fit=crop',
                'order' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($members as $member) {
            CommitteeMember::updateOrCreate(
                ['name' => $member['name'], 'position' => $member['position']],
                $member
            );
        }
    }
}
