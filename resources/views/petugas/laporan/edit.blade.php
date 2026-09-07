@extends('layouts.app')

@section('title', 'Edit Laporan')
@section('header-title', 'Edit Data Laporan')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
        <h2 class="text-lg font-bold text-gray-800">Edit Laporan Transaksi #{{ $peminjaman->id }}</h2>
        <a href="{{ route('petugas.laporan.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Kembali</a>
    </div>

    <div class="bg-gray-50 p-4 rounded-lg mb-6 text-sm space-y-1 border border-gray-200">
        <p><span class="font-medium text-gray-600">Peminjam:</span> {{ $peminjaman->user->name ?? '-' }}</p>
        <p><span class="font-medium text-gray-600">Tanggal Pinjam:</span> {{ $peminjaman->created_at->format('d M Y H:i') }}</p>
    </div>

    <form action="{{ route('petugas.laporan.update', $peminjaman->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Transaksi</label>
            <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5">
                <option value="diajukan" {{ $peminjaman->status == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                <option value="dipinjam" {{ $peminjaman->status == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="selesai" {{ $peminjaman->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="ditolak" {{ $peminjaman->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Denda (Rp)</label>
            <input type="number" name="total_denda" min="0" value="{{ old('total_denda', $peminjaman->total_denda ?? 0) }}" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Catatan</label>
            <textarea name="keterangan" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5">{{ old('keterangan', $peminjaman->keterangan) }}</textarea>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
            <a href="{{ route('petugas.laporan.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Update Laporan</button>
        </div>
    </form>
</div>
@endsection