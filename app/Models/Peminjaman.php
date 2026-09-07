<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';

    protected $fillable = [
        'user_id',
        'petugas_id', // <-- Ditambahkan agar petugas_id bisa disimpan ke database
        'tgl_pinjam',
        'tgl_kembali_plan',
        'tgl_kembali_real',
        'status',
        'keterangan',
        'total_denda',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pinjam' => 'date:Y-m-d',
            'tgl_kembali_plan' => 'date:Y-m-d',
            'tgl_kembali_real' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    // Relasi tambahan jika di database kamu memakai nama petugas_pinjam_id / petugas_kembali_id
    public function petugasPinjam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_pinjam_id');
    }

    public function petugasKembali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_kembali_id');
    }

    public function detailPinjams(): HasMany
    {
        return $this->hasMany(DetailPinjam::class);
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->detailPinjams();
    }

    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class);
    }
}