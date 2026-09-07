<?php

namespace Database\Seeders;

use App\Models\LogAktivitas;
use Illuminate\Database\Seeder;

class LogAktivitasSeeder extends Seeder
{
    public function run(): void
    {
        $logs = [
            [
                'user_id'       => 1, // Admin
                'aktivitas'     => 'Melakukan import data masster alat baru 5 entitas',
            ],
            [
                'user_id'       => 2, // Michael
                'aktivitas'     => 'Menyetujui pengajuan peminjaman ID #4',
            ],
            [
                'user_id'       => 3, // Jordan
                'aktivitas'     => 'Mengajukan peminjaman alat baru untuk kebutuhan praktik kelompok.',
            ],
            [
                'user_id'       => 2, // Michael
                'aktivitas'     => 'Memproses pengembalian alat telat untuk peminjaman ID #3 dan mengenakan denda.',
            ],
            [
                'user_id'       => 1, // Admin
                'aktivitas'     => 'Mengubah konfigurasi hak akses aplikasi.',
            ],
        ];

        foreach ($logs as $log) {
            LogAktivitas::create($log);
        }
    }
}