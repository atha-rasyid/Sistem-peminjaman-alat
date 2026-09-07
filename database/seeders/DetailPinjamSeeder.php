<?php

namespace Database\Seeders;

use App\Models\DetailPinjam;
use Illuminate\Database\Seeder;

class DetailPinjamSeeder extends Seeder
{
    public function run(): void
    {
        $detailPinjam = [ 
            [
                'peminjaman_id' => 1,
                'alat_id'       => 1,
                'jumlah'        => 2,   // Pinjam 2 Mikrotik
            ],
            [
                'peminjaman_id' => 2,
                'alat_id'       => 2,
                'jumlah'        => 1,   // Pinjam 1 Kamera DSLR
            ],
            [
                'peminjaman_id' => 3,
                'alat_id'       => 3,
                'jumlah'        => 1,   // Pinjam 1 Mini PC
            ],
            [
                'peminjaman_id' => 4,
                'alat_id'       => 4,
                'jumlah'        => 2,   // Pinjam 2 Tang Crimping
            ],
            [
                'peminjaman_id' => 5,
                'alat_id'       => 5,
                'jumlah'        => 3,   // Pinjam 3 Adapter HDMI to VGA
            ],
        ];

        foreach ($detailPinjam as $detail) {
            DetailPinjam::create($detail);
        }
    }
}