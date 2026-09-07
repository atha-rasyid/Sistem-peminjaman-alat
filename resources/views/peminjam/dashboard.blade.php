@extends('layouts.app')

@section('title', 'Dashboard Peminjam')
@section('header-title', 'Dashboard Peminjam')

@section('content')
<div class="space-y-6">

    <!-- WELCOME BANNER -->
    <div class="bg-indigo-700 text-white p-6 rounded-xl shadow-md flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">Selamat Datang, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-indigo-200 mt-1">Cari dan pinjam alat yang kamu butuhkan dengan cepat dan mudah.</p>
        </div>
        <a href="{{ route('peminjam.katalog') }}" 
           class="bg-white text-indigo-700 font-semibold px-5 py-2.5 rounded-lg hover:bg-indigo-50 transition shadow">
            + Pinjam Alat Baru
        </a>
    </div>

    <!-- CARDS STATISTIK RINGKAS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Total Peminjaman -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalPinjam }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl">
            </div>
        </div>

        <!-- Menunggu Persetujuan -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Menunggu Persetujuan</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $pending }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center font-bold text-xl">
            </div>
        </div>

        <!-- Sedang Dipinjam -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Sedang Dipinjam</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $sedangDipinjam }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-xl">
            </div>
        </div>

    </div>

    <!-- TABEL PEMINJAMAN TERBARU -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Riwayat Peminjaman Terakhir</h2>
            <a href="{{ route('peminjam.riwayat') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">
                Lihat Semua →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Tanggal Pinjam</th>
                        <th class="px-6 py-3">Rencana Kembali</th>
                        <th class="px-6 py-3">Jumlah Alat</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($peminjamanTerbaru as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ \Carbon\Carbon::parse($item->tgl_pinjam)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::parse($item->tgl_kembali_plan)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-md text-xs font-medium">
                                    {{ $item->detailPinjams->sum('jumlah') }} Unit
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($item->status == 'diajukan')
                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        Menunggu
                                    </span>
                                @elseif($item->status == 'disetujui')
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        Dipinjam
                                    </span>
                                @elseif($item->status == 'ditolak')
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        Ditolak
                                    </span>
                                @elseif($item->status == 'dikembalikan')
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-gray-400">
                                Belum ada riwayat peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection