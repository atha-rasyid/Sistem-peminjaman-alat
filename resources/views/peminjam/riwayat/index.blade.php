@extends('layouts.app') {{-- Ganti ke 'layouts.admin' atau sesuai nama file layout Anda jika berbeda --}}

@section('title', 'Riwayat Peminjaman & Pengembalian')
@section('header-title', 'Riwayat Saya')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Riwayat Peminjaman & Pengembalian</h1>
    </div>

    <!-- TABEL RIWAYAT -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Alat yang Dipinjam</th>
                        <th class="px-6 py-3">Tgl Pinjam / Rencana Kembali</th>
                        <th class="px-6 py-3">Status Peminjaman</th>
                        <th class="px-6 py-3">Keterangan / Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($peminjaman as $index => $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $index + 1 }}</td>
                            
                            <!-- DAFTAR ALAT -->
                            <td class="px-6 py-4">
                                <ul class="list-disc list-inside space-y-1">
                                    {{-- Mengakomodasi relasi detailPinjams maupun detailPeminjaman --}}
                                    @php
                                        $details = $item->detailPeminjaman ?? $item->detailPinjams ?? [];
                                    @endphp

                                    @forelse($details as $detail)
                                        <li class="text-gray-800">
                                            <span class="font-medium">{{ $detail->alat->nama_alat ?? 'Alat tidak ditemukan' }}</span>
                                            <span class="text-xs text-gray-500">({{ $detail->jumlah ?? 1 }} pcs)</span>
                                        </li>
                                    @empty
                                        <li class="text-xs text-gray-400 italic">Tidak ada detail alat</li>
                                    @endforelse
                                </ul>
                            </td>

                            <!-- TANGGAL -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <div><span class="font-semibold text-gray-700">Pinjam:</span> {{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d M Y') }}</div>
                                <div><span class="font-semibold text-gray-700">Rencana:</span> {{ \Carbon\Carbon::parse($item->tgl_kembali_plan)->format('d M Y') }}</div>
                                @if($item->tgl_kembali_real)
                                    <div class="text-green-600 font-semibold"><span class="text-gray-700">Kembali:</span> {{ \Carbon\Carbon::parse($item->tgl_kembali_real)->format('d M Y') }}</div>
                                @endif
                            </td>

                            <!-- STATUS BADGE -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClass = match(strtolower($item->status)) {
                                        'diajukan' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                        'dipinjam' => 'bg-blue-100 text-blue-800 border-blue-300',
                                        'selesai'  => 'bg-green-100 text-green-800 border-green-300',
                                        'ditolak'  => 'bg-red-100 text-red-800 border-red-300',
                                        default    => 'bg-gray-100 text-gray-800 border-gray-300'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <!-- KETERANGAN & DENDA -->
                            <td class="px-6 py-4 text-xs">
                                @if($item->keterangan)
                                    <div class="text-gray-600 italic mb-1">"{{ $item->keterangan }}"</div>
                                @endif

                                @if($item->total_denda && $item->total_denda > 0)
                                    <span class="inline-block bg-red-50 text-red-700 px-2 py-0.5 rounded font-semibold border border-red-200">
                                        Denda: Rp {{ number_format($item->total_denda, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 2 0 01-2-2V5a2 2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Belum ada riwayat peminjaman atau pengembalian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection