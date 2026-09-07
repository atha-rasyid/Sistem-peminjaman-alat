<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tgl_pinjam');
            $table->date('tgl_kembali_plan'); // Pastikan nama kolom ini sudah tgl_kembali_plan
            $table->date('tgl_kembali_real')->nullable();
            $table->enum('status', ['diajukan', 'dipinjam', 'dikembalikan', 'selesai', 'telat'])->default('diajukan');
            $table->text('keterangan')->nullable();
            $table->decimal('total_denda', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('peminjaman');
    }
};