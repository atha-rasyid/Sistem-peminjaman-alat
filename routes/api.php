<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KategoriController; 
use App\Http\Controllers\API\AlatController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LogAktivitasController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin Routes
    Route::middleware('role.admin')->prefix('admin')->name('admin.')->group(function () { 
        Route::apiResource('kategori', KategoriController::class);
        Route::apiResource('alat', AlatController::class);
        Route::get('/katalog',[AlatController::class, 'katalog']);
        Route::apiResource('users', UserController::class);
        Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class,'show']);
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
        Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update']);
        Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy']);
        Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class,'show']);
        Route::put('/pengembalian/{pengembalian}/approve', [PengembalianController::class, 'update']);
        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy']);
        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index']);
        Route::get('/laporan-peminjaman', [LaporanController::class, 'index'])->name('laporan.index');
    });

    // Petugas Routes (Ditambahkan prefix 'petugas' dan name 'petugas.')
    Route::middleware('role.petugas')->prefix('petugas')->name('petugas.')->group(function () {
        // Peminjaman
        Route::get('/peminjaman', [PetugasController::class, 'indexPeminjaman'])->name('peminjaman.index');
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('peminjaman.approve');
        Route::post('/peminjaman/{id}/setujui', [PetugasController::class, 'setujuiPeminjaman'])->name('peminjaman.setujui');
        Route::post('/peminjaman/{id}/tolak', [PetugasController::class, 'tolakPeminjaman'])->name('peminjaman.tolak');

        // Pengembalian
        Route::get('/pengembalian', [PetugasController::class, 'indexPengembalian'])->name('pengembalian.index');
        Route::put('/pengembalian', [PengembalianController::class, 'store'])->name('pengembalian.store');
        Route::get('/pengembalian/{id}/edit', [PetugasController::class, 'editPengembalian'])->name('pengembalian.edit');
        Route::put('/pengembalian/{id}', [PetugasController::class, 'updatePengembalian'])->name('pengembalian.update');

        // Laporan CRUD (Sesuai Method PetugasController)
        Route::get('/laporan', [PetugasController::class, 'indexLaporan'])->name('laporan.index');
        Route::get('/laporan/create', [PetugasController::class, 'createLaporan'])->name('laporan.create');
        Route::post('/laporan', [PetugasController::class, 'storeLaporan'])->name('laporan.store');
        Route::get('/laporan/{id}/edit', [PetugasController::class, 'editLaporan'])->name('laporan.edit');
        Route::put('/laporan/{id}', [PetugasController::class, 'updateLaporan'])->name('laporan.update');
        Route::delete('/laporan/{id}', [PetugasController::class, 'destroyLaporan'])->name('laporan.destroy');
    });

    // Peminjam Routes
    Route::middleware('role.peminjam')->prefix('peminjam')->name('peminjam.')->group(function () {
        Route::get('/dashboard', [PeminjamController::class, 'dashboard'])->name('dashboard');
        Route::get('/katalog', [PeminjamController::class, 'katalogAlat'])->name('katalog');
        Route::get('/peminjam/pinjam/{id}', [PeminjamController::class, 'formPinjam'])->name('peminjam.pinjam');
        Route::post('/peminjaman', [PeminjamController::class, 'prosesPeminjaman'])->name('pinjam.proses');
        Route::get('/riwayat-pinjam', [PeminjamController::class, 'riwayatPeminjaman'])->name('riwayat');
    });
});