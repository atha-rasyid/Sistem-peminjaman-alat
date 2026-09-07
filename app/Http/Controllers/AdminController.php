<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ==========================================
    // DASHBOARD & LOG
    // ==========================================

    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('logs'));
    }

    // Endpoint API JSON untuk Script Real-time
    public function fetchLogs()
    {
        $logs = LogAktivitas::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($log) {
                // Menyiapkan URL foto profil user
                $fotoProfil = null;
                if ($log->user && $log->user->foto) {
                    $fotoProfil = asset('storage/' . $log->user->foto);
                }

                return [
                    'waktu'     => $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i:s') : '-',
                    'user_name' => $log->user->name ?? 'Sistem',
                    'user_foto' => $fotoProfil,
                    'aktivitas' => $log->aktivitas,
                ];
            });

        return response()->json($logs);
    }

    // ==========================================
    // MANAJEMEN ALAT
    // ==========================================

    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    public function createAlat()
    {
        $kategoris = Kategori::all();

        return view('admin.alat.create', compact('kategoris'));
    }

    public function storeAlat(Request $request)
    {
        $validated = $request->validate([
            'kategori_id'    => 'required|exists:kategori,id',
            'nama_alat'      => 'required|string|max:255',
            'stok'           => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:255',
            'deskripsi'      => 'nullable|string',
            'denda'          => 'nullable|numeric|min:0',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('alat', 'public');
        }

        Alat::create($validated);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menambahkan alat baru: {$request->nama_alat}",
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Alat berhasil ditambahkan.');
    }

    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();

        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat'      => 'required|string|max:255',
            'kategori_id'    => 'required|exists:kategori,id',
            'stok'           => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:255',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'deskripsi'      => 'nullable|string',
        ]);

        $data = $request->only(['nama_alat', 'kategori_id', 'stok', 'status_kondisi', 'deskripsi']);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada di storage
            if ($alat->gambar && Storage::disk('public')->exists($alat->gambar)) {
                Storage::disk('public')->delete($alat->gambar);
            }

            // Simpan gambar baru
            $data['gambar'] = $request->file('gambar')->store('alat', 'public');
        }

        $alat->update($data);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Memperbarui data alat: {$alat->nama_alat}",
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $namaAlat = $alat->nama_alat;

        // Hapus file gambar dari storage jika ada
        if ($alat->gambar && Storage::disk('public')->exists($alat->gambar)) {
            Storage::disk('public')->delete($alat->gambar);
        }

        $alat->delete();

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus alat: {$namaAlat}",
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Alat berhasil dihapus.');
    }

    // ==========================================
    // MANAJEMEN USER
    // ==========================================

    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    public function storeUser(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'role'     => 'required|in:admin,petugas,peminjam',
        'no_hp'    => 'nullable|string|max:20',
        'foto'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Path foto default jika user tidak mengunggah foto
    $pathFoto = 'users/default.png';

    // Simpan Foto Profil jika ada yang diunggah
    if ($request->hasFile('foto')) {
        $pathFoto = $request->file('foto')->store('users', 'public');
    }

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'role'     => $request->role,
        'no_hp'    => $request->no_hp,
        'foto'     => $pathFoto, // <-- Selalu terisi (tidak akan NULL)
    ]);

    LogAktivitas::create([
        'user_id'   => auth()->id(),
        'aktivitas' => "Menambahkan user baru: {$user->name} ({$user->role})",
    ]);

    return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
}

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        return view('admin.user.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role'  => 'required|in:admin,petugas,peminjam',
            'no_hp' => 'nullable|string|max:20',
            'foto'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Simpan/Update Foto Profil User
        if ($request->hasFile('foto')) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $data['foto'] = $request->file('foto')->store('users', 'public');
        }

        $user->update($data);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Memperbarui data user: {$user->name}",
        ]);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $namaUser = $user->name;

        // Hapus Foto Profil dari Storage
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        $user->delete();

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus user: {$namaUser}",
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    // ==========================================
    // MANAJEMEN KATEGORI
    // ==========================================

    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            return $query->where('nama_kategori', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }

    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menambahkan kategori baru: {$kategori->nama_kategori}",
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        return view('admin.kategori.edit', compact('kategori'));
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Memperbarui kategori: {$kategori->nama_kategori}",
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alats()->exists()) {
            return redirect()->back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $namaKategori = $kategori->nama_kategori;
        $kategori->delete();

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus kategori: {$namaKategori}",
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // ==========================================
    // MANAJEMEN PEMINJAMAN
    // ==========================================

    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->when($search, function ($query, $search) {
                return $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get();
        $alats = Alat::where('stok', '>', 0)->get();

        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    public function storePeminjaman(Request $request)
{
    $request->validate([
        'user_id'          => 'required|exists:users,id',
        'tgl_pinjam'       => 'required|date',
        'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
        'alat_id'          => 'required|array',
        'alat_id.*'        => 'exists:alat,id',
        'jumlah'           => 'required|array',
        'jumlah.*'         => 'integer|min:1',
    ]);

    DB::beginTransaction();
    try {
        // Ambil 1 user acak dengan role petugas / admin
        $petugasRandom = User::whereIn('role', ['petugas', 'admin'])->inRandomOrder()->first();

        $peminjaman = Peminjaman::create([
            'user_id'           => $request->user_id,
            'tgl_pinjam'        => $request->tgl_pinjam,
            'tgl_kembali_plan'  => $request->tgl_kembali_plan,
            'status'            => 'diajukan',
            'petugas_pinjam_id' => $petugasRandom ? $petugasRandom->id : auth()->id(), // Petugas Random
        ]);

        foreach ($request->alat_id as $index => $alatId) {
            $jumlahPinjam = $request->jumlah[$index];
            $alat = Alat::findOrFail($alatId);

            if ($alat->stok < $jumlahPinjam) {
                throw new Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
            }

            DetailPinjam::create([
                'peminjaman_id' => $peminjaman->id,
                'alat_id'       => $alatId,
                'jumlah'        => $jumlahPinjam,
            ]);
        }

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Membuat transaksi peminjaman baru (ID: #{$peminjaman->id}) untuk User ID: {$peminjaman->user_id}",
        ]);

        DB::commit();

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
    } catch (Exception $e) {
        DB::rollBack();

        return back()->withInput()->with('error', $e->getMessage());
    }
}

public function updatePeminjaman(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:diajukan,dipinjam,selesai,telat',
    ]);

    DB::beginTransaction();
    try {
        $peminjaman = Peminjaman::with('detailPinjams.alat')->findOrFail($id);
        $statusLama = $peminjaman->status;
        $statusBaru = $request->status;

        $updateData = ['status' => $statusBaru];

        // 1. Kurangi stok alat saat persetujuan peminjaman (diajukan -> dipinjam)
        if ($statusLama === 'diajukan' && $statusBaru === 'dipinjam') {
            foreach ($peminjaman->detailPinjams as $detail) {
                if ($detail->alat) {
                    if ($detail->alat->stok < $detail->jumlah) {
                        throw new Exception("Stok alat '{$detail->alat->nama_alat}' tidak cukup.");
                    }
                    $detail->alat->decrement('stok', $detail->jumlah);
                }
            }
        }

        // 2. Kembalikan stok & Assign Petugas Pengembalian Random jika status berubah menjadi 'selesai'
        if ($statusLama !== 'selesai' && $statusBaru === 'selesai') {
            foreach ($peminjaman->detailPinjams as $detail) {
                if ($detail->alat) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            // Pilih petugas pengembalian secara random jika belum terisi
            if (!$peminjaman->petugas_kembali_id) {
                $petugasKembaliRandom = User::whereIn('role', ['petugas', 'admin'])->inRandomOrder()->first();
                $updateData['petugas_kembali_id'] = $petugasKembaliRandom ? $petugasKembaliRandom->id : auth()->id();
            }

            $updateData['tgl_kembali_real'] = now();
        } 
        
        // 3. Jika status dibatalkan kembali dari 'selesai' ke 'dipinjam' (stok dikurangi lagi)
        elseif ($statusLama === 'selesai' && $statusBaru === 'dipinjam') {
            foreach ($peminjaman->detailPinjams as $detail) {
                if ($detail->alat) {
                    $detail->alat->decrement('stok', $detail->jumlah);
                }
            }
        }

        // Update peminjaman dengan data baru
        $peminjaman->update($updateData);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Mengubah status peminjaman ID #{$peminjaman->id} dari '{$statusLama}' menjadi '{$statusBaru}'",
        ]);

        DB::commit();

        return redirect()->back()->with('success', 'Status peminjaman berhasil diperbarui.');
    } catch (Exception $e) {
        DB::rollBack();

        return redirect()->back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
    }
}

    public function riwayatPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with([
                'user', 
                'detailPinjams.alat', 
                'petugasPinjam', 
                'petugasKembali'
            ])
            // Jika ada pencarian
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('status', 'like', "%{$search}%");
            })
            ->latest() // Mengurutkan dari transaksi terbaru
            ->paginate(10);

        // Mengirimkan variabel $peminjamans ke Blade view
        return view('admin.peminjaman.index', compact('peminjamans'));
    }

    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjams.alat')->findOrFail($id);

        if ($peminjaman->status === 'dipinjam') {
            foreach ($peminjaman->detailPinjams as $detail) {
                if ($detail->alat) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }
        }

        $idPeminjaman = $peminjaman->id;
        $peminjaman->delete();

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus transaksi peminjaman ID #{$idPeminjaman}",
        ]);

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

    // ==========================================
    // MANAJEMEN PENGEMBALIAN & DENDA
    // ==========================================

    public function indexpengembalian(Request $request)
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

        return view('admin.pengembalian.index', compact('pengembalians', 'search'));
    }

    public function editPengembalian($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat'])->findOrFail($id);

        $tglReal = now()->toDateString();
        $tglRencana = $peminjaman->tgl_kembali_plan ?? $peminjaman->tgl_kembali;
        $dendaTerlambat = Pengembalian::hitungDendaKeterlambatan($tglRencana, $tglReal);

        return view('admin.pengembalian.edit', compact('peminjaman', 'dendaTerlambat'));
    }

    public function updatePengembalian(Request $request, $id)
    {
        $request->validate([
            'tgl_kembali_real' => 'required|date',
            'denda_per_unit'   => 'required|array',
            'denda_per_unit.*' => 'numeric|min:0',
            'kondisi'          => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams.alat')->findOrFail($id);

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

                if (in_array($peminjaman->status, ['dipinjam', 'telat'])) {
                    if ($detail->alat) {
                        $detail->alat->increment('stok', $detail->jumlah);
                    }
                }
            }

            $totalDendaPeminjaman = $dendaKeterlambatan + $totalDendaKerusakan;

            $peminjaman->update([
                'tgl_kembali_real' => $request->tgl_kembali_real,
                'total_denda'      => $totalDendaPeminjaman,
                'status'           => 'selesai',
            ]);

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Memproses pengembalian alat untuk Peminjaman ID #{$peminjaman->id} (Total Denda: Rp" . number_format($totalDendaPeminjaman, 0, ',', '.') . ")",
            ]);

            DB::commit();

            return redirect()->route('admin.pengembalian.index')->with('success', 'Pengembalian berhasil diproses.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}