@extends('layouts.app')

@section('title', 'Tambah Alat - Panel Admin')
@section('header-title', 'Tambah Alat Baru')

@section('content')
<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.alat.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Alat</label>
            <input type="text" name="nama_alat" value="{{ old('nama_alat') }}" placeholder="Contoh: Proyektor Epson, Tang Kombinasi" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('nama_alat') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Kategori Alat</label>
            <select name="kategori_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
            @error('kategori_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Stok Alat</label>
                <input type="number" name="stok" value="{{ old('stok', 1) }}" min="0" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('stok') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Status / Kondisi</label>
                <select name="status_kondisi" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Baik" {{ old('status_kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                    <option value="Rusak Ringan" {{ old('status_kondisi') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="Rusak Berat" {{ old('status_kondisi') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
                @error('status_kondisi') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Denda (Rp / hari)</label>
            <input type="number" name="denda" value="{{ old('denda', 0) }}" min="0" placeholder="0"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('denda') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Gambar Alat</label>
            <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:outline-none">
            <span class="text-gray-400 text-xs mt-1 block">Format: JPG, JPEG, PNG (Maks. 2MB)</span>
            @error('gambar') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi Alat</label>
            <textarea name="deskripsi" rows="3" placeholder="Tambahkan rincian atau spesifikasi alat..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.alat.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">
                Batal
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                Simpan Alat
            </button>
        </div>
    </form>
</div>
@endsection