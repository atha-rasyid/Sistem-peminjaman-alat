@extends('layouts.app') {{-- sesuaikan dengan nama file layout utama Anda --}}

@section('title', 'Pemantauan Pengembalian - Petugas')
@section('header-title', 'Pemantauan Pengembalian Alat')

@section('content')
<div class="space-y-6">

    <!-- Flash Message Notification -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Pencarian (berlaku untuk ketiga daftar) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex gap-6 text-sm text-gray-600">
            <span><strong class="text-yellow-600 text-lg">{{ $peminjamanDiajukan->total() }}</strong> peminjaman diajukan</span>
            <span><strong class="text-blue-600 text-lg">{{ $pengembalianDiajukan->total() }}</strong> menunggu pengembalian</span>
            <span><strong class="text-emerald-600 text-lg">{{ $pengembalians->total() }}</strong> pengembalian selesai</span>
        </div>

        <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama peminjam..."
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-64">
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('petugas.pengembalian.index') }}"
                   class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-300 transition flex items-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- 1. PEMINJAMAN YANG DIAJUKAN -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">Peminjaman yang Diajukan</h2>
            <p class="text-xs text-gray-500 mt-0.5">Pengajuan yang menunggu persetujuan petugas.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold tracking-wider">
                        <th class="py-3 px-4">Peminjam</th>
                        <th class="py-3 px-4">Tgl Pinjam</th>
                        <th class="py-3 px-4">Rencana Kembali</th>
                        <th class="py-3 px-4">Detail Alat</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($peminjamanDiajukan as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-semibold text-gray-900">{{ $item->user->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d-m-Y') }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($item->tgl_kembali_plan)->format('d-m-Y') }}</td>
                            <td class="py-3 px-4">
                                <ul class="list-disc list-inside">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} <span class="text-gray-500 font-semibold">({{ $detail->jumlah }} unit)</span></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4">
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">Diajukan</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                {{-- Tombol setujui hanya muncul jika route-nya ada di proyek Anda --}}
                                @if(\Illuminate\Support\Facades\Route::has('petugas.peminjaman.update'))
                                    <form action="{{ route('petugas.peminjaman.update', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Setujui peminjaman ini?')">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="dipinjam">
                                        <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                                            Setujui
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 italic">Menunggu persetujuan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-400">Tidak ada peminjaman yang diajukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $peminjamanDiajukan->links() }}
        </div>
    </div>

    <!-- 2. PENGEMBALIAN YANG DIAJUKAN (alat masih dipinjam, menunggu diproses) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">Pengembalian yang Diajukan</h2>
            <p class="text-xs text-gray-500 mt-0.5">Alat yang masih dipinjam dan menunggu proses pengembalian.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold tracking-wider">
                        <th class="py-3 px-4">Peminjam</th>
                        <th class="py-3 px-4">Tgl Pinjam</th>
                        <th class="py-3 px-4">Rencana Kembali</th>
                        <th class="py-3 px-4">Detail Alat</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($pengembalianDiajukan as $item)
                        @php
                            $rencana = \Carbon\Carbon::parse($item->tgl_kembali_plan);
                            $terlambat = $item->status === 'telat' || $rencana->lt(today());
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-semibold text-gray-900">{{ $item->user->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d-m-Y') }}</td>
                            <td class="py-3 px-4">{{ $rencana->format('d-m-Y') }}</td>
                            <td class="py-3 px-4">
                                <ul class="list-disc list-inside">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} <span class="text-gray-500 font-semibold">({{ $detail->jumlah }} unit)</span></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4">
                                @if($terlambat)
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">Terlambat</span>
                                @else
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">Dipinjam</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('petugas.pengembalian.edit', $item->id) }}"
                                   class="inline-block bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-emerald-700 transition shadow-sm">
                                    Proses pengembalian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-400">Tidak ada alat yang menunggu pengembalian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $pengembalianDiajukan->links() }}
        </div>
    </div>

    <!-- 3. DAFTAR PENGEMBALIAN ALAT (sudah selesai) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">Daftar Pengembalian Alat</h2>
            <p class="text-xs text-gray-500 mt-0.5">Riwayat pengembalian yang sudah selesai diproses.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold tracking-wider">
                        <th class="py-3 px-4">Peminjam</th>
                        <th class="py-3 px-4">Tgl Pinjam</th>
                        <th class="py-3 px-4">Tgl Kembali</th>
                        <th class="py-3 px-4">Detail Alat</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Total Denda</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($pengembalians as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-semibold text-gray-900">{{ $item->user->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d-m-Y') }}</td>
                            <td class="py-3 px-4">
                                {{ $item->tgl_kembali_real ? \Carbon\Carbon::parse($item->tgl_kembali_real)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <ul class="list-disc list-inside">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} <span class="text-gray-500 font-semibold">({{ $detail->jumlah }} unit)</span></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4">
                                @if($item->petugasKembali)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-800 rounded-md text-xs font-medium border border-gray-200">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ $item->petugasKembali->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if(($item->total_denda ?? 0) > 0)
                                    <span class="text-red-600 font-semibold">Rp{{ number_format($item->total_denda, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('petugas.pengembalian.edit', $item->id) }}"
                                   class="inline-block bg-gray-100 text-gray-700 border border-gray-300 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-gray-200 transition">
                                    Lihat detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-400">Belum ada pengembalian yang selesai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $pengembalians->links() }}
        </div>
    </div>
</div>
@endsection