@extends('layouts.app')

@section('title', 'Kelola Pengembalian Alat')
@section('header-title', 'Konfirmasi Pengembalian Alat')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Daftar Pengajuan Pengembalian</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola dan konfirmasi pengembalian alat dari peminjam.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                    <th class="py-3.5 px-6 font-semibold">No</th>
                    <th class="py-3.5 px-6 font-semibold">Peminjam</th>
                    <th class="py-3.5 px-6 font-semibold">Alat yang Dipinjam</th>
                    <th class="py-3.5 px-6 font-semibold">Tgl Rencana Kembali</th>
                    <th class="py-3.5 px-6 font-semibold">Status</th>
                    <th class="py-3.5 px-6 font-semibold">Keterangan / Denda</th>
                    <th class="py-3.5 px-6 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                @forelse ($peminjamans as $index => $item)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="py-4 px-6 font-medium">{{ $index + 1 }}</td>
                        <td class="py-4 px-6">
                            <div class="font-medium text-gray-900">{{ $item->user->name ?? 'User' }}</div>
                            <div class="text-xs text-gray-500">{{ $item->user->email ?? '' }}</div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-medium text-gray-900">{{ $item->alat->nama_alat ?? 'Alat' }}</span>
                            <span class="text-xs text-gray-500 block">({{ $item->jumlah ?? 1 }} pcs)</span>
                        </td>
                        <td class="py-4 px-6 text-gray-600">
                            {{ \Carbon\Carbon::parse($item->tanggal_rencana_kembali)->format('d M Y') }}
                        </td>
                        <td class="py-4 px-6">
                            @if ($item->status == 'diajukan')
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold">Diajukan</span>
                            @elseif ($item->status == 'diproses')
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Di proses</span>
                            @elseif ($item->status == 'selesai')
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold">Selesai</span>
                            @elseif ($item->status == 'telat')
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-semibold">Telat</span>
                            @else
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">{{ ucfirst($item->status) }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            @if ($item->denda > 0)
                                <span class="text-xs font-semibold text-rose-600 bg-rose-50 px-2 py-1 rounded border border-rose-200">
                                    Denda: Rp {{ number_format($item->denda, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if ($item->status == 'diajukan' || $item->status == 'diproses')
                                <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition shadow-sm">
                                        Konfirmasi Selesai / Telat
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400 italic">Selesai dikonfirmasi</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400 text-sm">
                            Tidak ada data pengembalian yang perlu dikonfirmasi saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection