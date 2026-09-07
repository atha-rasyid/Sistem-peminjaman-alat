@extends('layouts.app')

@section('title', 'Laporan Transaksi')
@section('header-title', 'Laporan Transaksi Peminjaman')

@section('content')
<div class="space-y-6">

    <!-- Ringkasan Stat & Filter -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Transaksi (Bulan Ini)</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalPeminjaman ?? 0 }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Denda (Bulan Ini)</p>
            <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($totalDendaBulanIni ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Laporan Manual</p>
                <p class="text-xs text-gray-400">Buat / catat laporan baru</p>
            </div>
            <a href="{{ route('petugas.laporan.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                + Tambah
            </a>
        </div>
    </div>

    <!-- Filter Bulan & Tahun + Cetak PDF -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-wrap justify-between items-center gap-4">
        <form method="GET" action="{{ route('petugas.laporan.index') }}" class="flex flex-wrap gap-4 items-center flex-1">
            <div>
                <select name="bulan" class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" {{ ($bulan ?? date('m')) == sprintf('%02d', $m) ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <select name="tahun" class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ ($tahun ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari peminjam..." class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2">
            </div>
            <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                Filter
            </button>
        </form>

        <!-- Tombol Cetak PDF -->
        <a href="{{ route('petugas.laporan.cetak', ['bulan' => $bulan ?? date('m'), 'tahun' => $tahun ?? date('Y'), 'search' => $search ?? '']) }}" 
           target="_blank" 
           class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak PDF
        </a>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabel Laporan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">#</th>
                        <th class="p-4">Peminjam</th>
                        <th class="p-4">Barang</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Total Denda</th>
                        <th class="p-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-medium">#{{ $item->id }}</td>
                            <td class="p-4">
                                <div class="font-semibold text-gray-800">{{ $item->user->name ?? 'User Terhapus' }}</div>
                                <div class="text-xs text-gray-400">{{ optional($item->created_at)->format('d M Y') ?? '-' }}</div>
                            </td>
                            <td class="p-4">
                                <ul class="list-disc list-inside text-xs space-y-0.5">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="p-4">
                                @if($item->status == 'selesai')
                                    <span class="px-2.5 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Selesai</span>
                                @elseif($item->status == 'dipinjam')
                                    <span class="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">Dipinjam</span>
                                @elseif($item->status == 'ditolak')
                                    <span class="px-2.5 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">Ditolak</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full">{{ ucfirst($item->status) }}</span>
                                @endif
                            </td>
                            <td class="p-4 font-medium text-red-600">
                                Rp {{ number_format($item->total_denda ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('petugas.laporan.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs">Edit</a>
                                    <form action="{{ route('petugas.laporan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus laporan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-400">Tidak ada data laporan untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-100">
            {{ $peminjamans->links() }}
        </div>
    </div>
</div>
@endsection