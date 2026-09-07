@extends('layouts.app')

@section('title', 'Edit Alat - Panel Admin')
@section('header-title', 'Edit Data Alat')

@section('content')
<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <!-- Ditambahkan enctype="multipart/form-data" untuk unggah file -->
    <form action="{{ route('admin.alat.update', $alat->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Alat</label>
            <input type="text" name="nama_alat" value="{{ old('nama_alat', $alat->nama_alat) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('nama_alat') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Kategori Alat</label>
            <select name="kategori_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ old('kategori_id', $alat->kategori_id) == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
            @error('kategori_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Stok Alat</label>
            <input type="number" name="stok" value="{{ old('stok', $alat->stok) }}" min="0" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('stok') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Status / Kondisi</label>
            <select name="status_kondisi" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Baik" {{ old('status_kondisi', $alat->status_kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option>
                <option value="Rusak Ringan" {{ old('status_kondisi', $alat->status_kondisi) == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                <option value="Rusak Berat" {{ old('status_kondisi', $alat->status_kondisi) == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
            </select>
            @error('status_kondisi') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Input Upload Gambar + Preview Gambar Saat Ini -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Foto / Gambar Alat</label>
            
            @if($alat->gambar)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ asset('storage/' . $alat->gambar) }}" alt="{{ $alat->nama_alat }}" 
                         class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                    <span class="text-xs text-gray-500">Gambar saat ini. Pilih file baru jika ingin mengganti.</span>
                </div>
            @endif

            <input type="file" name="gambar" accept="image/png, image/jpeg, image/jpg"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:outline-none">
            <span class="text-xs text-gray-400 mt-1 block">Format: PNG, JPG, JPEG (Maks. 2MB)</span>
            @error('gambar') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi Alat</label>
            <textarea name="deskripsi" rows="3" placeholder="Tambahkan deskripsi atau catatan alat..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi', $alat->deskripsi) }}</textarea>
            @error('deskripsi') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.alat.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">
                Batal
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                Perbarui Alat
            </button>
        </div>
    </form>
</div>
@endsection