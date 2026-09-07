@extends('layouts.app')

@section('title', 'Detail Pengembalian Alat')
@section('header-title', 'Detail Pengembalian Alat')

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

    <div class="space-y-6">
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

        <!-- Detail Kondisi Barang & Denda (Read-Only) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
            <h3 class="text-base font-bold text-gray-800 border-b pb-2">Kondisi Barang & Denda</h3>

            <!-- Tanggal Realisasi Pengembalian -->
            <div>
                <span class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengembalian Riil:</span>
                <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 w-full md:w-1/2">
                    {{ $peminjaman->tgl_kembali_real ? \Carbon\Carbon::parse($peminjaman->tgl_kembali_real)->format('d-m-Y') : '-' }}
                </div>
            </div>

            <!-- List Barang yang Dipinjam -->
            <div class="pt-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pemeriksaan Kondisi Alat:</label>
                <div class="space-y-4">
                    @foreach($peminjaman->detailPinjams as $detail)
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="md:w-1/3">
                                <h4 class="font-bold text-gray-800">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</h4>
                                <span class="text-xs text-gray-500">Jumlah: {{ $detail->jumlah }} unit</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:w-2/3">
                                <!-- Status Kondisi -->
                                <div>
                                    <span class="block text-xs font-semibold text-gray-600 mb-1">Kondisi Alat</span>
                                    <div class="px-3 py-1.5 bg-white border border-gray-200 rounded-md text-sm font-medium text-gray-800">
                                        @if(($detail->kondisi ?? 'baik') == 'baik')
                                            <span class="text-green-600">Baik</span>
                                        @elseif(($detail->kondisi ?? '') == 'rusak_ringan')
                                            <span class="text-yellow-600">Rusak Ringan</span>
                                        @elseif(($detail->kondisi ?? '') == 'rusak_berat')
                                            <span class="text-red-600">Rusak Berat / Hilang</span>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Denda Kerusakan / Unit -->
                                <div>
                                    <span class="block text-xs font-semibold text-gray-600 mb-1">Denda Kerusakan / Unit</span>
                                    <div class="px-3 py-1.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800">
                                        Rp {{ number_format($detail->denda_per_unit ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Catatan Tambahan -->
            <div class="pt-2">
                <span class="block text-sm font-semibold text-gray-700 mb-1">Catatan Petugas</span>
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 whitespace-pre-line min-h-[60px]">
                    {{ $peminjaman->keterangan ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Utama -->
        <div class="flex justify-end">
            <a href="{{ route('petugas.pengembalian.index') }}" class="px-5 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection