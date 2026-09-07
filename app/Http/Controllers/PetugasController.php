<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use App\Models\LogAktivitas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PetugasController extends Controller
{
    // Menampilkan daftar pengajuan peminjaman dari siswa/peminjam
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }

    // Menolak pengajuan peminjaman
    public function tolakPeminjaman($id) 
    {
        try {
            $peminjaman = Peminjaman::with('user')->findOrFail($id);

            if ($peminjaman->status == 'diajukan') {
                $userName = $peminjaman->user->name ?? 'User';
                
                // Ubah status ke ditolak
                $peminjaman->update(['status' => 'ditolak']);

                // Catat Log Aktivitas Petugas
                LogAktivitas::create([
                    'user_id'   => auth()->id(),
                    'aktivitas' => "Petugas menolak pengajuan peminjaman ID #{$peminjaman->id} (User: {$userName})",
                ]);

                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
            }

            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Menyetujui Peminjaman (Mengubah status & mengurangi stok alat)
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat'])->findOrFail($id);

            // Validasi ketersediaan stok sebelum menyetujui
            foreach ($peminjaman->detailPinjams as $detail) {
                if ($detail->alat->stok < $detail->jumlah) {
                    return redirect()->back()->with('error', "Stok alat {$detail->alat->nama_alat} tidak mencukupi (Tersisa: {$detail->alat->stok}).");
                }
            }

            // Update status peminjaman
            $peminjaman->update(['status' => 'dipinjam']);

            // Kurangi stok alat secara otomatis
            foreach ($peminjaman->detailPinjams as $detail) {
                $detail->alat->decrement('stok', $detail->jumlah);
            }

            // Catat Log Aktivitas Petugas
            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Petugas menyetujui peminjaman ID #{$peminjaman->id} (User: {$peminjaman->user->name})",
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat berhasil dikurangi.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Menampilkan daftar pengembalian
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalians = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->whereIn('status', ['dipinjam', 'selesai', 'telat'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petugas.pengembalian.index', compact('pengembalians', 'search'));
    }

    // Form proses pengembalian alat (Cek Kondisi & Denda)
    public function editPengembalian($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat'])->findOrFail($id);
        $tglReal = now()->toDateString();
        $tglRencana = $peminjaman->tgl_kembali_plan ?? $peminjaman->tgl_kembali;
        
        // Hitung denda keterlambatan jika ada
        $dendaTerlambat = Pengembalian::hitungDendaKeterlambatan($tglRencana, $tglReal);

        return view('petugas.pengembalian.edit', compact('peminjaman', 'dendaTerlambat'));
    }

    // Eksekusi Simpan Pengembalian
    public function updatePengembalian(Request $request, $id)
    {
        $request->validate([
            'tgl_kembali_real' => 'required|date',
            'keterangan'       => 'nullable|string',
            'denda_per_unit'   => 'required|array',
            'denda_per_unit.*' => 'numeric|min:0',
            'kondisi'          => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat'])->findOrFail($id);

            $tglRencana = $peminjaman->tgl_kembali_plan ?? $peminjaman->tgl_kembali;
            $dendaKeterlambatan = Pengembalian::hitungDendaKeterlambatan(
                $tglRencana,
                $request->tgl_kembali_real
            );

            $totalDendaKerusakan = 0;

            foreach ($peminjaman->detailPinjams as $detail) {
                $dendaUnit = $request->denda_per_unit[$detail->id] ?? 0;
                $kondisi   = $request->kondisi[$detail->id] ?? 'baik';
                $subtotalDendaKerusakan = $dendaUnit * $detail->jumlah;

                $detail->update([
                    'denda_per_unit'  => $dendaUnit,
                    'subtotal_denda'  => $subtotalDendaKerusakan,
                    'kondisi_kembali' => $kondisi,
                ]);

                $totalDendaKerusakan += $subtotalDendaKerusakan;

                // Kembalikan stok alat jika status sebelumnya masih dipinjam/telat
                if (in_array($peminjaman->status, ['dipinjam', 'telat'])) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            $totalDendaPeminjaman = $dendaKeterlambatan + $totalDendaKerusakan;

            $peminjaman->update([
                'tgl_kembali_real' => $request->tgl_kembali_real,
                'keterangan'       => $request->keterangan,
                'total_denda'      => $totalDendaPeminjaman,
                'status'           => 'selesai',
            ]);

            // Catat Log Aktivitas Petugas
            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Petugas memproses pengembalian peminjaman ID #{$peminjaman->id} (User: {$peminjaman->user->name})",
            ]);

            DB::commit();
            return redirect()->route('petugas.pengembalian.index')->with('success', 'Pengembalian barang berhasil diproses.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ==========================================
    // FITUR LAPORAN
    // ==========================================

    // 1. Tampilkan Laporan per Bulan & Filter Tahun
    public function indexLaporan(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Ringkasan Statistik Laporan Bulanan
        $totalPeminjaman = $peminjamans->total();
        $totalDendaBulanIni = Peminjaman::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->sum('total_denda');

        return view('petugas.laporan.index', compact('peminjamans', 'bulan', 'tahun', 'search', 'totalPeminjaman', 'totalDendaBulanIni'));
    }

    // 2. Form Tambah Laporan Manual / Catatan Tambahan Laporan
    public function createLaporan()
    {
        $peminjamans = Peminjaman::with('user')->latest()->get();
        return view('petugas.laporan.create', compact('peminjamans'));
    }

    // 3. Simpan Laporan Manual Baru
    public function storeLaporan(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjamans,id',
            'keterangan'    => 'required|string',
            'total_denda'   => 'nullable|numeric|min:0',
        ]);

        try {
            $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);
            $peminjaman->update([
                'keterangan'  => $request->keterangan,
                'total_denda' => $request->total_denda ?? $peminjaman->total_denda,
            ]);

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Petugas menambahkan laporan manual untuk Peminjaman ID #{$peminjaman->id}",
            ]);

            return redirect()->route('petugas.laporan.index')->with('success', 'Laporan berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    // 4. Form Edit Laporan
    public function editLaporan($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat'])->findOrFail($id);
        return view('petugas.laporan.edit', compact('peminjaman'));
    }

    // 5. Eksekusi Update Laporan
    public function updateLaporan(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|string',
            'keterangan'  => 'nullable|string',
            'total_denda' => 'required|numeric|min:0',
        ]);

        try {
            $peminjaman = Peminjaman::findOrFail($id);
            $peminjaman->update([
                'status'      => $request->status,
                'keterangan'  => $request->keterangan,
                'total_denda' => $request->total_denda,
            ]);

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Petugas mengubah laporan Peminjaman ID #{$peminjaman->id}",
            ]);

            return redirect()->route('petugas.laporan.index')->with('success', 'Laporan berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    // 6. Hapus Laporan / Record Peminjaman
    public function destroyLaporan($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);
            $peminjamanId = $peminjaman->id;
            
            $peminjaman->delete();

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Petugas menghapus record laporan Peminjaman ID #{$peminjamanId}",
            ]);

            return redirect()->route('petugas.laporan.index')->with('success', 'Data laporan berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus laporan: ' . $e->getMessage());
        }
    }
public function cetakLaporan(Request $request)
{
    $bulan = $request->get('bulan', date('m'));
    $tahun = $request->get('tahun', date('Y'));
    $search = $request->get('search');

    $query = Peminjaman::with(['user', 'detailPinjams.alat'])
        ->whereMonth('created_at', $bulan)
        ->whereYear('created_at', $tahun);

    if ($search) {
        $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
    }

    $peminjamans = $query->get();
    $totalPeminjaman = $peminjamans->count();
    $totalDendaBulanIni = $peminjamans->sum('total_denda');

    // Opsi A: Menggunakan DomPDF package (Download/Stream PDF)
    $pdf = Pdf::loadView('petugas.laporan.pdf', compact(
        'peminjamans', 'bulan', 'tahun', 'totalPeminjaman', 'totalDendaBulanIni'
    ));
    return $pdf->stream("Laporan-Peminjaman-{$bulan}-{$tahun}.pdf");

    // Opsi B: Tanpa Package (Cetak langsung via browser / window.print())
    // return view('petugas.laporan.pdf', compact('peminjamans', 'bulan', 'tahun', 'totalPeminjaman', 'totalDendaBulanIni'));
}

}