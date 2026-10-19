<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    /**
     * Dashboard Peminjam
     */
    public function dashboard()
    {
        $userId = auth()->id();

        // Mengambil statistik ringkas untuk dipasang di dashboard
        $totalPinjam    = Peminjaman::where('user_id', $userId)->count();
        $sedangDipinjam = Peminjaman::where('user_id', $userId)->where('status', 'disetujui')->count();
        $pending        = Peminjaman::where('user_id', $userId)->where('status', 'diajukan')->count();

        // Mengambil 5 transaksi peminjaman terakhir
        $peminjamanTerbaru = Peminjaman::with('detailPinjams.alat')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        return view('peminjam.dashboard', compact('totalPinjam', 'sedangDipinjam', 'pending', 'peminjamanTerbaru'));
    }

    /**
     * Katalog Alat
     */
    public function katalogAlat(Request $request)
    {
        $query = Alat::with('kategori');

        // Filter pencarian jika ada
        if ($request->filled('search')) {
            $query->where('nama_alat', 'like', '%' . $request->search . '%');
        }

        // Filter kategori jika ada
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        $alat         = $query->paginate(12)->withQueryString();
        $kategoriList = Kategori::all();

        return view('peminjam.katalog.index', compact('alat', 'kategoriList'));
    }

    /**
     * Form Pinjam Multi-Item (Mendukung Multi-Select dari Katalog)
     */
    public function formPinjam(Request $request)
    {
        // Tangkap daftar ID alat dari query string (contoh: ?alat_ids=1,2,3)
        $alatIds = explode(',', $request->input('alat_ids'));
        
        // Ambil data alat berdasarkan kumpulan ID tersebut
        $alats = Alat::whereIn('id', $alatIds)->get();

        // Jika tidak ada alat yang dipilih, kembalikan ke katalog dengan pesan error
        if ($alats->isEmpty()) {
            return redirect()->route('peminjam.katalog')->with('error', 'Silakan pilih minimal satu alat terlebih dahulu.');
        }

        return view('peminjam.pinjam', compact('alats'));
    }

    /**
     * Proses Peminjaman Single (Dari Form Peminjaman Satuan)
     */
    public function storeFormPinjam(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'alat_id'         => 'required|exists:alat,id',
            'jumlah'          => 'required|integer|min:1|max:5',
            'tanggal_pinjam'  => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
            'alasan'          => 'nullable|string',
        ]);

        // 2. Cek Stok Alat
        $alat = Alat::findOrFail($request->alat_id);

        if ($request->jumlah > $alat->stok) {
            return back()->withInput()->with('error', 'Jumlah peminjaman melebihi stok yang tersedia.');
        }

        // 3. Transaksi Database
        DB::beginTransaction();
        try {
            // Simpan Header Peminjaman
            $peminjaman = Peminjaman::create([
                'user_id'          => auth()->id(),
                'tgl_pinjam'       => $request->tanggal_pinjam,
                'tgl_kembali_plan' => $request->tanggal_kembali,
                'alasan'           => $request->alasan ?? '-',
                'status'           => 'diajukan',
            ]);

            // Simpan Detail Peminjaman
            DetailPinjam::create([
                'peminjaman_id' => $peminjaman->id,
                'alat_id'       => $request->alat_id,
                'jumlah'        => $request->jumlah,
            ]);

            DB::commit();

            return redirect()->route('peminjam.riwayat')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    /**
     * Proses Peminjaman Multi-Item (Dari Katalog Alat Baru)
     */
    public function prosesPeminjaman(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after_or_equal:today',
            'alat_id'          => 'required|array|min:1',
            'alat_id.*'        => 'required|exists:alat,id',
            'jumlah'           => 'required|array',
            'jumlah.*'         => 'required|integer|min:1|max:5', // Batasan maksimal 5 unit per jenis alat
        ], [
            'tgl_kembali_plan.required' => 'Silakan tentukan rencana tanggal pengembalian terlebih dahulu.',
            'alat_id.required'          => 'Pilih minimal satu alat sebelum mengajukan peminjaman.',
            'jumlah.*.max'              => 'Jumlah peminjaman maksimal adalah 5 unit per jenis alat.',
        ]);

        // 2. Validasi Stok Alat
        foreach ($request->alat_id as $index => $alatId) {
            $alat = Alat::find($alatId);
            $qty  = $request->jumlah[$index];

            if ($alat && $qty > $alat->stok) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Jumlah peminjaman untuk {$alat->nama_alat} melebihi stok yang tersedia ({$alat->stok} unit).");
            }
        }

        // 3. Simpan Peminjaman
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id'          => auth()->id(),
                'tgl_pinjam'       => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status'           => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id'       => $alatId,
                    'jumlah'        => $request->jumlah[$index],
                ]);
            }

            DB::commit();

            return redirect()->route('peminjam.riwayat')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    /**
     * Riwayat Peminjaman
     */
    public function riwayatPeminjaman()
    {
        $peminjaman = Peminjaman::with('detailPinjams.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.riwayat.index', compact('peminjaman'));
    }

    /**
     * Mengajukan Pengembalian Alat oleh Peminjam
     */
    public function ajukanKembali($id)
    {
        // Hanya boleh untuk peminjaman milik user yang login
        $peminjaman = Peminjaman::where('user_id', auth()->id())->findOrFail($id);

        if (!in_array($peminjaman->status, ['dipinjam', 'telat'])) {
            return back()->with('error', 'Peminjaman ini tidak dapat dikembalikan.');
        }

        // Ubah status menjadi 'dikembalikan' atau 'diajukan' sesuai alur konfirmasi petugas
        $peminjaman->update(['status' => 'dikembalikan']);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Mengajukan pengembalian untuk peminjaman ID #{$peminjaman->id}",
        ]);

        return redirect()->route('peminjam.riwayat')
            ->with('success', 'Pengajuan pengembalian dikirim. Menunggu konfirmasi petugas.');
    }
}