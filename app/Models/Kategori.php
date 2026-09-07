<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori'; // Sesuaikan dengan nama tabel Anda (kategori / kategoris)

    protected $fillable = [
        'nama_kategori',
    ];

    // Tambahkan relasi ke model Alat
    public function alats()
    {
        return $this->hasMany(Alat::class, 'kategori_id');
    }
}