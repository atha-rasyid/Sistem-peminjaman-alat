<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alat';

    protected $fillable = [
        'kategori_id',
        'nama_alat',
        'stok',
        'status_kondisi',
        'deskripsi',
        'denda',
        'gambar',
    ];

    /**
     * Denda keterlambatan global (Rp 10.000 / 3 hari)
     */
    public const DENDA_KETERLAMBATAN_PER_3_HARI = 10000;

    /**
     * Menghitung kelipatan 3 hari (misal: 1-3 hari = 10rb, 4-6 hari = 20rb)
     */
    public static function hitungDendaKeterlambatan(int $jumlahHariTerlambat): float|int
    {
        if ($jumlahHariTerlambat <= 0) {
            return 0;
        }

        $periode = (int) ceil($jumlahHariTerlambat / 3);

        return $periode * self::DENDA_KETERLAMBATAN_PER_3_HARI;
    }

    /**
     * Cast atribut ke tipe data tertentu.
     */
    protected function casts(): array
    {
        return [
            'stok'  => 'integer',
            'denda' => 'float',
        ];
    }

    /**
     * Relasi ke Kategori
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id');
    }

    /**
     * Relasi ke DetailPinjam
     */
    public function detailPinjam(): HasMany
    {
        return $this->hasMany(DetailPinjam::class, 'alat_id', 'id');
    }

    /**
     * Scope untuk menyaring alat yang stoknya tersedia (> 0)
     */
    public function scopeBersedia(Builder $query): Builder
    {
        return $query->where('stok', '>', 0);
    }
}