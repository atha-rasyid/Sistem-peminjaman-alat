<?php

namespace Database\Seeders;

use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        $peminjaman = [
            [
                'user_id'          => 3,                // Jordan
                'tgl_pinjam'       => '2026-06-01',
                'tgl_kembali_plan' => '2026-06-04',
                'tgl_kembali_real' => '2026-06-04',
                'status'           => 'selesai',
            ],
            [
                'user_id'          => 4,                // Kiara
                'tgl_pinjam'       => '2026-06-02',
                'tgl_kembali_plan' => '2026-06-05',
                'tgl_kembali_real' => '2026-06-05',
                'status'           => 'selesai',
            ],
            [
                'user_id'          => 5,                // Cantika
                'tgl_pinjam'       => '2026-06-03',
                'tgl_kembali_plan' => '2026-06-06',
                'status'           => 'telat',
            ],
            [
                'user_id'          => 3,                // Jordan
                'tgl_pinjam'       => '2026-06-08',
                'tgl_kembali_plan' => '2026-06-11',
                'status'           => 'dipinjam',
            ],
            [
                'user_id'          => 4,                // Kiara
                'tgl_pinjam'       => '2026-06-09',
                'tgl_kembali_plan' => '2026-06-12',
                'status'           => 'diajukan',
            ],
        ];

        foreach ($peminjaman as $pinjam) {
            Peminjaman::create($pinjam);
        }
    }
}