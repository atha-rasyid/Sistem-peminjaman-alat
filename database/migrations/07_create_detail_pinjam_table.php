<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('detail_pinjam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjaman')->cascadeOnDelete();
            $table->foreignId('alat_id')->constrained('alat')->cascadeOnDelete();
            $table->integer('jumlah')->default(1);
            $table->decimal('denda_per_unit', 12, 2)->default(0);
            $table->decimal('subtotal_denda', 12, 2)->default(0);
            $table->string('kondisi_kembali')->default('baik');
            $table->timestamps();
        });
    }

    public function down(): void{
        Schema::dropIfExists('detail_pinjam');
    }
};
