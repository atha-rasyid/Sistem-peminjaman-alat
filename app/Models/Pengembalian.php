<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengembalian extends Model
{
    protected $table = 'pengembalian';

    protected $fillable = [
        'peminjaman_id',
        'tgl_kembali',      // Tanggal rencana kembali
        'tgl_kembali_real', // Tanggal aktual barang dikembalikan
        'kondisi_kembali',
        'keterangan',       // Catatan / Keterangan dari admin
        'denda',
        'petugas_id',
    ];

    public const DENDA_PER_HARI = 5000;

    protected function casts(): array
    {
        return [
            'tgl_kembali'      => 'date:Y-m-d',
            'tgl_kembali_real' => 'date:Y-m-d',
            'denda'            => 'integer',
        ];
    }

    public static function hitungDendaKeterlambatan($tglRencana, $tglReal): int
    {
        if (!$tglRencana || !$tglReal) {
            return 0;
        }

        $rencana = Carbon::parse($tglRencana)->startOfDay();
        $real    = Carbon::parse($tglReal)->startOfDay();

        // Jika dikembalikan tepat waktu atau lebih cepat, denda 0
        if ($real->lessThanOrEqualTo($rencana)) {
            return 0;
        }

        // Hitung selisih hari keterlambatan
        $hariTerlambat = $rencana->diffInDays($real);

        return (int) ($hariTerlambat * self::DENDA_PER_HARI);
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}