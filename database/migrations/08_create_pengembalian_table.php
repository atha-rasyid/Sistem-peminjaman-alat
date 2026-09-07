<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('pengembalian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjaman')->cascadeOnDelete();
            $table->date('tgl_kembali');
            $table->date('tgl_kembali_real')->nullable(); // Kolom tanggal real pengembalian
            $table->string('kondisi_kembali')->default('baik');
            $table->text('keterangan')->nullable(); // Kolom keterangan admin
            $table->decimal('denda', 12, 2)->default(0);
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pengembalian');
    }
};
