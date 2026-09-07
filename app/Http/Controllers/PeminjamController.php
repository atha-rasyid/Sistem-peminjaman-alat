<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    // METHOD UNTUK HALAMAN DASHBOARD PEMINJAM
    public function dashboard()
    {
        $userId = auth()->id();

        // Mengambil statistik ringkas untuk ditampilkan di dashboard
        $totalPinjam = Peminjaman::where('user_id', $userId)->count();
        $sedangDipinjam = Peminjaman::where('user_id', $userId)->where('status', 'disetujui')->count();
        $pending = Peminjaman::where('user_id', $userId)->where('status', 'diajukan')->count();

        // Mengambil 5 transaksi peminjaman terakhir
        $peminjamanTerbaru = Peminjaman::with('detailPinjams.alat')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        return view('peminjam.dashboard', compact('totalPinjam', 'sedangDipinjam', 'pending', 'peminjamanTerbaru'));
    }

    // METHOD KATALOG ALAT
    public function katalogAlat(Request $request)
    {
        // 2. Query data alat beserta relasinya
        $query = Alat::with('kategori');

        // Filter pencarian jika ada
        if ($request->filled('search')) {
            $query->where('nama_alat', 'like', '%' . $request->search . '%');
        }

        // Filter kategori jika ada
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        $alats = $query->paginate(12)->withQueryString();

        // 3. Ambil seluruh data kategori untuk dropdown filter
        $kategoriList = Kategori::all();

        // 4. Kirim keduannya ($alats DAN $kategoriList) ke view
        return view('peminjam.katalog.index', compact('alats', 'kategoriList'));
    }

    public function formPinjam($id)
    {
        $alat = Alat::findOrFail($id);
    
        return view('peminjam.pinjam', compact('alat'));
    }

    // METHOD PROSES PEMINJAMAN
    public function prosesPeminjaman(Request $request) 
    {
        $request->validate([
            'tgl_kembali_plan'  => 'required|date|after:today',
            'alat_id'           => 'required|array',
            'jumlah'            => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id'          => auth()->id(),
                'tgl_pinjam'       => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status'           => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatid) {
                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id'       => $alatid,
                    'jumlah'        => $request->jumlah[$index]
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    // METHOD RIWAYAT PEMINJAMAN
    public function riwayatPeminjaman()
    {
        $peminjaman = Peminjaman::with('detailPeminjaman.alat')
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->get();

    // Ubah menjadi peminjam.riwayat.index
        return view('peminjam.riwayat.index', compact('peminjaman'));
    }
}