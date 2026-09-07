@extends('layouts.app')

@section('title', 'Proses Pengembalian - Panel Admin')
@section('header-title', 'Proses Pengembalian & Input Denda')

@section('content')
    <div class="max-w-4xl bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Form Pengembalian & Denda</h3>
        </div>

        <form action="{{ route('admin.pengembalian.update', $peminjaman->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Informasi Peminjam -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm">
                <div>
                    <p class="text-gray-500">Nama Peminjam:</p>
                    <p class="font-bold text-gray-800 text-base">{{ $peminjaman->user->name ?? 'User Dihapus' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Rencana Kembali:</p>
                    <p class="font-bold text-gray-800 text-base">{{ $peminjaman->tgl_kembali_plan }}</p>
                </div>
            </div>

            <!-- Tanggal Pengembalian Real -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pengembalian Real</label>
                <input type="date" name="tgl_kembali_real" value="{{ old('tgl_kembali_real', $peminjaman->tgl_kembali_real ?? date('Y-m-d')) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-800">
            </div>

            <!-- Table Detail Alat & Input Denda -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Detail Alat Dipinjam & Denda</label>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                                <th class="p-3 border border-gray-200 text-left">Nama Alat</th>
                                <th class="p-3 border border-gray-200 text-center">Jumlah</th>
                                <th class="p-3 border border-gray-200 text-center">Kondisi</th>
                                <th class="p-3 border border-gray-200 text-left">Denda per Unit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($peminjaman->detailPinjams ?? [] as $detail)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 border border-gray-200 font-medium text-gray-800">
                                        {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                    </td>
                                    <td class="p-3 border border-gray-200 text-center font-bold text-gray-700">
                                        {{ $detail->jumlah }}
                                    </td>
                                    <td class="p-3 border border-gray-200">
                                        <select name="kondisi[{{ $detail->id }}]" class="w-full border border-gray-300 p-1.5 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                            <option value="baik" {{ ($detail->kondisi_kembali ?? 'baik') == 'baik' ? 'selected' : '' }}>Baik</option>
                                            <option value="rusak" {{ ($detail->kondisi_kembali ?? '') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                            <option value="hilang" {{ ($detail->kondisi_kembali ?? '') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                                        </select>
                                    </td>
                                    <td class="p-3 border border-gray-200">
                                        <input type="number" name="denda_per_unit[{{ $detail->id }}]" value="{{ old('denda_per_unit.'.$detail->id, $detail->denda_per_unit ?? 0) }}" min="0" step="500"
                                            class="w-full p-1.5 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-gray-500 italic">Tidak ada detail alat yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Keterangan / Catatan Admin -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan / Catatan Admin</label>
                <textarea name="keterangan" rows="3" placeholder="Contoh: Terlambat 2 hari, 1 unit alat tergores ringan..."
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-800">{{ old('keterangan', $peminjaman->keterangan) }}</textarea>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.pengembalian.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Simpan Pengembalian
                </button>
            </div>
        </form>
    </div>
@endsection