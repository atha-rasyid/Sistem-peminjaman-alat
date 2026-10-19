<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PeminjamController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [AuthController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');

// ==========================================
// ADMIN ROUTES
// ==========================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/logs/fetch', [AdminController::class, 'fetchLogs'])->name('logs.fetch');

    // CRUD Alat
    Route::get('/alat', [AdminController::class, 'indexAlat'])->name('alat.index');
    Route::get('/alat/create', [AdminController::class, 'createAlat'])->name('alat.create');
    Route::post('/alat', [AdminController::class, 'storeAlat'])->name('alat.store');
    Route::get('/alat/{id}/edit', [AdminController::class, 'editAlat'])->name('alat.edit');
    Route::put('/alat/{id}', [AdminController::class, 'updateAlat'])->name('alat.update');
    Route::delete('/alat/{id}', [AdminController::class, 'destroyAlat'])->name('alat.destroy');

    // CRUD User
    Route::get('/users', [AdminController::class, 'indexUser'])->name('user.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('user.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('user.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('user.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('user.destroy');

    // CRUD Kategori
    Route::get('/kategori', [AdminController::class, 'indexkategori'])->name('kategori.index');
    Route::get('/kategori/create', [AdminController::class, 'createkategori'])->name('kategori.create');
    Route::post('/kategori', [AdminController::class, 'storekategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [AdminController::class, 'editkategori'])->name('kategori.edit');
    Route::put('/kategori/{id}', [AdminController::class, 'updatekategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'destroykategori'])->name('kategori.destroy');

    // CRUD Peminjaman
    Route::get('/peminjaman', [AdminController::class, 'indexpeminjaman'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [AdminController::class, 'createpeminjaman'])->name('peminjaman.create');
    Route::post('/peminjaman', [AdminController::class, 'storepeminjaman'])->name('peminjaman.store');
    Route::get('/peminjaman/{id}/edit', [AdminController::class, 'editpeminjaman'])->name('peminjaman.edit');
    Route::put('/peminjaman/{id}', [AdminController::class, 'updatePeminjaman'])->name('peminjaman.update');
    Route::delete('/peminjaman/{id}', [AdminController::class, 'destroypeminjaman'])->name('peminjaman.destroy');

    // CRUD Pengembalian 
    Route::get('/pengembalian', [AdminController::class, 'indexpengembalian'])->name('pengembalian.index');
    Route::get('/pengembalian/create', [AdminController::class, 'createpengembalian'])->name('pengembalian.create');
    Route::post('/pengembalian', [AdminController::class, 'storepengembalian'])->name('pengembalian.store');
    Route::get('/pengembalian/{id}/edit', [AdminController::class, 'editpengembalian'])->name('pengembalian.edit');
    Route::put('/pengembalian/{id}', [AdminController::class, 'updatepengembalian'])->name('pengembalian.update');
    Route::delete('/pengembalian/{id}', [AdminController::class, 'destroypengembalian'])->name('pengembalian.destroy');
});

// ==========================================
// PETUGAS ROUTES
// ==========================================
Route::middleware(['auth', 'role:petugas,admin'])->prefix('petugas')->name('petugas.')->group(function () {
    // Peminjaman & Persetujuan
    Route::get('/peminjaman', [PetugasController::class, 'indexPeminjaman'])->name('peminjaman.index');
    Route::post('/peminjaman/{id}/setujui', [PetugasController::class, 'setujuiPeminjaman'])->name('peminjaman.setujui');
    Route::post('/peminjaman/{id}/tolak', [PetugasController::class, 'tolakPeminjaman'])->name('peminjaman.tolak');

    // Pengembalian & Denda
    Route::get('/pengembalian', [PetugasController::class, 'indexPengembalian'])->name('pengembalian.index');
    Route::post('/pengembalian/{id}', [PetugasController::class, 'prosesPengembalian'])->name('pengembalian.proses');

    // Pemantauan pengembalian
    Route::get('/pengembalian/{id}/edit', [PetugasController::class, 'editPengembalian'])->name('pengembalian.edit');
    Route::put('/pengembalian/{id}', [PetugasController::class, 'updatePengembalian'])->name('pengembalian.update');

    // Cetak Laporan
    Route::get('/laporan/cetak', [PetugasController::class, 'cetakLaporan'])->name('laporan.cetak');

    // Laporan CRUD (Sudah disesuaikan dengan Blade)
    Route::get('/laporan', [PetugasController::class, 'indexLaporan'])->name('laporan.index');
    Route::get('/laporan/create', [PetugasController::class, 'createLaporan'])->name('laporan.create');
    Route::post('/laporan', [PetugasController::class, 'storeLaporan'])->name('laporan.store');
    Route::get('/laporan/{id}/edit', [PetugasController::class, 'editLaporan'])->name('laporan.edit');
    Route::put('/laporan/{id}', [PetugasController::class, 'updateLaporan'])->name('laporan.update');
    Route::delete('/laporan/{id}', [PetugasController::class, 'destroyLaporan'])->name('laporan.destroy');
});

// ==========================================
// PEMINJAM ROUTES
// ==========================================
Route::middleware(['auth', 'role:peminjam'])->prefix('peminjam')->name('peminjam.')->group(function () {
    Route::get('/dashboard', [PeminjamController::class, 'dashboard'])->name('dashboard');
    Route::get('/katalog', [PeminjamController::class, 'katalogAlat'])->name('katalog');
    
    // Route form pinjam multi-item menggunakan query string (tanpa parameter {id})
    Route::get('/pinjam', [PeminjamController::class, 'formPinjam'])->name('pinjam');
    
    Route::post('/pinjam', [PeminjamController::class, 'storeFormPinjam'])->name('pinjam.store'); 
    
    Route::get('/riwayat', [PeminjamController::class, 'riwayatPeminjaman'])->name('riwayat');
    Route::post('/peminjaman', [PeminjamController::class, 'prosesPeminjaman'])->name('pinjam.proses');
    Route::post('/proses-peminjaman', [PeminjamController::class, 'prosesPeminjaman'])->name('proses_peminjaman'); 

    Route::put('/riwayat/{id}/kembalikan', [PeminjamController::class, 'ajukanKembali'])->name('kembalikan');
});

// ==========================================
// AUTHENTICATION ROUTES
// ==========================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');