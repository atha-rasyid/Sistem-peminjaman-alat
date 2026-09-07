<?php

namespace Database\Seeders;

use App\Models\Pengembalian;
use Illuminate\Database\Seeder;

class PengembalianSeeder extends Seeder
{
    public function run(): void
    {
        $pengembalian = [
            [
                'peminjaman_id'    => 1, 
                'tgl_kembali'      => '2026-06-04', // Tanggal Rencana Kembali
                'tgl_kembali_real' => '2026-06-04', // Tanggal Pengembalian Barang
                'kondisi_kembali'  => 'baik',
                'keterangan'       => 'Lengkap dan Berfungsi Baik',
                'denda'            => 0,
                'petugas_id'       => 2, // Michael (Petugas)
            ],
            [
                'peminjaman_id'    => 2, 
                'tgl_kembali'      => '2026-06-05',
                'tgl_kembali_real' => '2026-06-05',
                'kondisi_kembali'  => 'baik',
                'keterangan'       => 'Lengkap dan Berfungsi Baik',
                'denda'            => 0,
                'petugas_id'       => 2,
            ],
            [
                'peminjaman_id'    => 3, 
                'tgl_kembali'      => '2026-06-06',
                'tgl_kembali_real' => '2026-06-09',
                'kondisi_kembali'  => 'rusak',
                'keterangan'       => 'Lengkap, Casing sedikit tergores. Telat 3 hari (Denda Rp 10.000/hari)',
                'denda'            => 30000,
                'petugas_id'       => 2,
            ],
        ];

        foreach ($pengembalian as $kembali) {
            Pengembalian::create($kembali);
        }
    }
}