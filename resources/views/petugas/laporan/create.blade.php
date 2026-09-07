@extends('layouts.app')

@section('title', 'Tambah Laporan')
@section('header-title', 'Tambah Catatan Laporan')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
        <h2 class="text-lg font-bold text-gray-800">Form Laporan Manual</h2>
        <a href="{{ route('petugas.laporan.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Kembali</a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('petugas.laporan.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Transaksi Peminjaman</label>
            <select name="peminjaman_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5" required>
                <option value="">-- Pilih ID Peminjaman --</option>
                @foreach($peminjamans as $p)
                    <option value="{{ $p->id }}">#{{ $p->id }} - {{ $p->user->name ?? 'User' }} ({{ $p->created_at->format('d/m/Y') }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Denda (Optional / Rp)</label>
            <input type="number" name="total_denda" min="0" value="0" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Catatan Petugas</label>
            <textarea name="keterangan" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 border p-2.5" placeholder="Tulis catatan kondisi atau denda..." required></textarea>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
            <a href="{{ route('petugas.laporan.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Simpan Laporan</button>
        </div>
    </form>
</div>
@endsection