@extends('layouts.app')

@section('title', 'Tambah User - Panel Admin')
@section('header-title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <!-- Form Tambah User -->
    <form action="{{ route('admin.user.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Field Upload Foto Profil -->
        <div class="mb-5">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Foto Profil (Opsional)</label>
            <div class="flex items-center gap-4">
                <!-- Preview Placeholder Icon / Avatar -->
                <div class="w-16 h-16 rounded-full bg-gray-100 text-gray-400 font-bold flex items-center justify-center border-2 border-dashed border-gray-300 shrink-0 overflow-hidden" id="preview-container">
                    <svg id="preview-icon" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <img id="preview-img" class="hidden w-full h-full object-cover" alt="Preview Foto">
                </div>

                <!-- Input File -->
                <div class="flex-1">
                    <input type="file" name="foto" id="foto" accept="image/*" onchange="previewImage(event)"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, JPEG, PNG (Maks. 2MB)</p>
                </div>
            </div>
            @error('foto') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('name') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('email') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
            <input type="password" name="password" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('password') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Role / Hak Akses</label>
            <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="peminjam" {{ old('role') == 'peminjam' ? 'selected' : '' }}>Peminjam</option>
                <option value="petugas" {{ old('role') == 'petugas' ? 'selected' : '' }}>Petugas</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            @error('role') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">No. HP (Opsional)</label>
            <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('no_hp') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.user.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Simpan</button>
        </div>
    </form>
</div>

<!-- Script Live Preview Gambar -->
<script>
    function previewImage(event) {
        const reader = new FileReader();
        const icon = document.getElementById('preview-icon');
        const img = document.getElementById('preview-img');

        reader.onload = function() {
            img.src = reader.result;
            img.classList.remove('hidden');
            icon.classList.add('hidden');
        }

        if (event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endsection