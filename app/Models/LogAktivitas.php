<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'aktivitas',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public static function catat(string $aktivitas, ?int $userId = null): void
    {
        self::create([
            'user_id' => $userId ?? auth()->id(),
            'aktivitas' => $aktivitas,
        ]);
    }
}