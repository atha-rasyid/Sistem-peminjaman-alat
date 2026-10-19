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
                
                $peminjaman->update(['status' => 'ditolak']);

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

    // Menyetujui Peminjaman
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat'])->findOrFail($id);

            foreach ($peminjaman->detailPinjams as $detail) {
                if ($detail->alat->stok < $detail->jumlah) {
                    return redirect()->back()->with('error', "Stok alat {$detail->alat->nama_alat} tidak mencukupi (Tersisa: {$detail->alat->stok}).");
                }
            }

            $peminjaman->update(['status' => 'dipinjam']);

            foreach ($peminjaman->detailPinjams as $detail) {
                $detail->alat->decrement('stok', $detail->jumlah);
            }

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

        $query = fn (array $status) => Peminjaman::with(['user', 'detailPinjams.alat', 'petugasKembali'])
            ->whereIn('status', $status)
            ->when($search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")));

        $peminjamanDiajukan   = $query(['diajukan'])->oldest()->paginate(5, ['*'], 'page_diajukan')->withQueryString();
        $pengembalianDiajukan = $query(['dipinjam', 'telat'])->orderBy('tgl_kembali_plan')->paginate(5, ['*'], 'page_kembali')->withQueryString();
        $pengembalians        = $query(['selesai'])->latest('tgl_kembali_real')->paginate(10, ['*'], 'page_riwayat')->withQueryString();

        return view('petugas.pengembalian.index', compact('peminjamanDiajukan', 'pengembalianDiajukan', 'pengembalians'));
    }

    // Menampilkan form edit / proses konfirmasi pengembalian alat
    public function editPengembalian($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat'])->findOrFail($id);
        
        $tglRencana = $peminjaman->tgl_kembali_plan ?? $peminjaman->tgl_kembali;
        $dendaTerlambat = Pengembalian::hitungDendaKeterlambatan(
            $tglRencana,
            date('Y-m-d')
        );

        return view('petugas.pengembalian.edit', compact('peminjaman', 'dendaTerlambat'));
    }

    // Eksekusi Update & Konfirmasi Pengembalian Menjadi Selesai
    public function updatePengembalian(Request $request, $id)
    {
        $request->validate([
            'tgl_kembali_real' => 'required|date',
            'keterangan'       => 'nullable|string',
            'details'          => 'required|array',
            'details.*.id'     => 'required|exists:detail_pinjam,id', // Diperbaiki dari detail_pinjams ke detail_pinjam
            'details.*.kondisi'=> 'required|string',
            'details.*.denda_per_unit' => 'nullable|numeric|min:0',
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

            foreach ($request->details as $item) {
                $detail = \App\Models\DetailPinjam::findOrFail($item['id']);
                $dendaUnit = $item['denda_per_unit'] ?? 0;
                $kondisi   = $item['kondisi'] ?? 'baik';
                
                // Perhitungan subtotal denda per item
                $subtotalDendaKerusakan = $dendaUnit * $detail->jumlah;

                $detail->update([
                    'denda_per_unit'  => $dendaUnit,
                    'subtotal_denda'  => $subtotalDendaKerusakan,
                    'kondisi'         => $kondisi,
                ]);

                $totalDendaKerusakan += $subtotalDendaKerusakan;

                // Kembalikan stok alat jika status sebelumnya dipinjam/telat
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

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Petugas mengonfirmasi pengembalian peminjaman ID #{$peminjaman->id} (User: {$peminjaman->user->name})",
            ]);

            DB::commit();
            return redirect()->route('petugas.pengembalian.index')->with('success', 'Pengembalian alat berhasil dikonfirmasi dan status diubah menjadi selesai.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==========================================
    // FITUR LAPORAN
    // ==========================================
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

        $totalPeminjaman = $peminjamans->total();
        $totalDendaBulanIni = Peminjaman::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->sum('total_denda');

        return view('petugas.laporan.index', compact('peminjamans', 'bulan', 'tahun', 'search', 'totalPeminjaman', 'totalDendaBulanIni'));
    }

    public function createLaporan()
    {
        $peminjamans = Peminjaman::with('user')->latest()->get();
        return view('petugas.laporan.create', compact('peminjamans'));
    }

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

    public function editLaporan($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjam.alat'])->findOrFail($id);
        return view('petugas.laporan.edit', compact('peminjaman'));
    }

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

        $pdf = Pdf::loadView('petugas.laporan.pdf', compact(
            'peminjamans', 'bulan', 'tahun', 'totalPeminjaman', 'totalDendaBulanIni'
        ));
        return $pdf->stream("Laporan-Peminjaman-{$bulan}-{$tahun}.pdf");
    }
}