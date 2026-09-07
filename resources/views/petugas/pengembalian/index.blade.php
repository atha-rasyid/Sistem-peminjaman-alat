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

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header & Pencarian (Judul di Kiri, Form Cari di Kanan) -->
        <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-gray-800 text-left">Daftar Pengembalian Alat</h2>

            <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama peminjam..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-64">
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition">
                    Cari
                </button>
            </form>
        </div>

        <!-- Tabel Pemantauan -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold tracking-wider">
                        <th class="py-3 px-4">Peminjam</th>
                        <th class="py-3 px-4">Tgl Pinjam</th>
                        <th class="py-3 px-4">Rencana Kembali</th>
                        <th class="py-3 px-4">Detail Alat</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($pengembalians as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-semibold text-gray-900">{{ $item->user->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d-m-Y') }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($item->tgl_kembali_plan ?? $item->tgl_kembali)->format('d-m-Y') }}</td>
                            <td class="py-3 px-4">
                                <ul class="list-disc list-inside">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} <span class="text-gray-500 font-semibold">({{ $detail->jumlah }} unit)</span></li>
                                    @endforeach
                                </ul>
                            </td>
                            <!-- MENAMPILKAN PETUGAS YANG MENGATASI -->
                            <td class="py-3 px-4">
                                @if($item->petugas)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-800 rounded-md text-xs font-medium border border-gray-200">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ $item->petugas->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($item->status === 'dipinjam')
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">Dipinjam</span>
                                @elseif($item->status === 'telat')
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">Terlambat</span>
                                @elseif($item->status === 'selesai')
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Selesai</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if(in_array($item->status, ['dipinjam', 'telat']))
                                    <a href="{{ route('petugas.pengembalian.edit', $item->id) }}" 
                                       class="inline-block bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-emerald-700 transition shadow-sm">
                                        Lihat detail
                                    </a>
                                @else
                                    <a href="{{ route('petugas.pengembalian.edit', $item->id) }}" 
                                       class="inline-block bg-gray-100 text-gray-700 border border-gray-300 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-gray-200 transition">
                                        Lihat detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-400">Tidak ada pengembalian yang perlu dipantau.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100">
            {{ $pengembalians->links() }}
        </div>
    </div>
</div>
@endsection