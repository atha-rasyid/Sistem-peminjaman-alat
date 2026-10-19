@extends('layouts.app')

@section('title', 'Konfirmasi Pengembalian Alat')
@section('header-title', 'Konfirmasi Pengembalian Alat')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Tombol Kembali -->
    <div>
        <a href="{{ route('petugas.pengembalian.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
            &larr; Kembali ke daftar pengembalian
        </a>
    </div>

    <!-- Alert Notifications -->
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Utama untuk Konfirmasi Pengembalian -->
    <form action="{{ route('petugas.pengembalian.update', $peminjaman->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Mengirimkan status 'selesai' secara otomatis saat submit -->
        <input type="hidden" name="status" value="selesai">

        <!-- Informasi Peminjaman -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-base font-bold text-gray-800 mb-4 border-b pb-2">Detail Transaksi Peminjaman</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 block">Peminjam:</span>
                    <strong class="text-gray-900 text-base">{{ $peminjaman->user->name ?? 'N/A' }}</strong>
                </div>
                <div>
                    <span class="text-gray-500 block">Tanggal Pinjam:</span>
                    <strong class="text-gray-800">{{ \Carbon\Carbon::parse($peminjaman->tgl_pinjam)->format('d-m-Y') }}</strong>
                </div>
                <div>
                    <span class="text-gray-500 block">Target Kembali (Rencana):</span>
                    <strong class="text-gray-800">{{ \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan ?? $peminjaman->tgl_kembali)->format('d-m-Y') }}</strong>
                </div>
                <div>
                    <span class="text-gray-500 block">Denda Keterlambatan:</span>
                    <strong class="text-red-600">Rp {{ number_format($dendaTerlambat ?? 0, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <!-- Kondisi Barang & Denda (Editable) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
            <h3 class="text-base font-bold text-gray-800 border-b pb-2">Kondisi Barang & Denda</h3>

            <!-- Tanggal Realisasi Pengembalian -->
            <div>
                <label for="tgl_kembali_real" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengembalian Riil:</label>
                <input type="date" name="tgl_kembali_real" id="tgl_kembali_real" 
                    value="{{ old('tgl_kembali_real', $peminjaman->tgl_kembali_real ? \Carbon\Carbon::parse($peminjaman->tgl_kembali_real)->format('Y-m-d') : date('Y-m-d')) }}" 
                    class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 w-full md:w-1/2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <!-- List Barang yang Dipinjam -->
            <div class="pt-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pemeriksaan Kondisi Alat:</label>
                <div class="space-y-4">
                    @if($peminjaman->detailPinjams && count($peminjaman->detailPinjams) > 0)
                        @foreach($peminjaman->detailPinjams as $index => $detail)
                            <input type="hidden" name="details[{{ $index }}][id]" value="{{ $detail->id }}">
                            
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="md:w-1/3">
                                    <h4 class="font-bold text-gray-800">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</h4>
                                    <span class="text-xs text-gray-500">Jumlah: {{ $detail->jumlah }} unit</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:w-2/3">
                                    <!-- Status Kondisi -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kondisi Alat</label>
                                        <select name="details[{{ $index }}][kondisi]" class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                            <option value="baik" {{ (old("details.$index.kondisi", $detail->kondisi) == 'baik') ? 'selected' : '' }}>Baik</option>
                                            <option value="rusak_ringan" {{ (old("details.$index.kondisi", $detail->kondisi) == 'rusak_ringan') ? 'selected' : '' }}>Rusak Ringan</option>
                                            <option value="rusak_berat" {{ (old("details.$index.kondisi", $detail->kondisi) == 'rusak_berat') ? 'selected' : '' }}>Rusak Berat / Hilang</option>
                                        </select>
                                    </div>

                                    <!-- Denda Kerusakan / Unit & Opsi Mata Uang -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Denda Kerusakan / Unit</label>
                                        <div class="flex gap-1">
                                            <select name="details[{{ $index }}][mata_uang]" class="px-2 py-1.5 bg-white border border-gray-300 rounded-md text-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                <option value="IDR" {{ (old("details.$index.mata_uang", $detail->mata_uang ?? 'IDR') == 'IDR') ? 'selected' : '' }}>Rp (IDR)</option>
                                                <option value="USD" {{ (old("details.$index.mata_uang", $detail->mata_uang ?? 'IDR') == 'USD') ? 'selected' : '' }}>$ (USD)</option>
                                            </select>
                                            <input type="number" step="any" name="details[{{ $index }}][denda_per_unit]" 
                                                value="{{ old("details.$index.denda_per_unit", $detail->denda_per_unit ?? 0) }}" 
                                                class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none" min="0" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500 italic">Tidak ada detail barang pada peminjaman ini.</p>
                    @endif
                </div>
            </div>

            <!-- Catatan Tambahan -->
            <div class="pt-2">
                <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-1">Catatan Petugas</label>
                <textarea name="keterangan" id="keterangan" rows="3" 
                    class="w-full p-3 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Masukkan catatan jika ada...">{{ old('keterangan', $peminjaman->keterangan) }}</textarea>
            </div>
        </div>

        <!-- Tombol Aksi Utama -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('petugas.pengembalian.index') }}" class="px-5 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition">
                Batal
            </a>
            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin mengonfirmasi pengembalian alat ini?')" class="px-5 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Konfirmasi
            </button>
        </div>
    </form>
</div>
@endsection