@extends('layouts.app')

@section('title', 'Edit User - Panel Admin')
@section('header-title', 'Edit Data Pengguna')

@section('content')
<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <!-- Form update user -->
    <form action="{{ route('admin.user.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Field Upload Foto Profil -->
        <div class="mb-5">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Foto Profil</label>
            <div class="flex items-center gap-4">
                <!-- Preview Foto Saat Ini -->
                <div class="relative">
                    @if(!empty($user->foto) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->foto))
                        <img src="{{ asset('storage/' . $user->foto) }}" alt="{{ $user->name }}" 
                             class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                    @elseif(!empty($user->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar))
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" 
                             class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                    @else
                        <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-lg border-2 border-blue-200 uppercase">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                    @endif
                </div>

                <!-- Input File -->
                <div class="flex-1">
                    <input type="file" name="foto" id="foto" accept="image/*"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, JPEG, PNG (Maks. 2MB)</p>
                </div>
            </div>
            @error('foto')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Password Baru
                <span class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah password)</span>
            </label>
            <input type="password" name="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Role / Hak Akses</label>
            <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="peminjam" {{ old('role', $user->role) == 'peminjam' ? 'selected' : '' }}>Peminjam</option>
                <option value="petugas" {{ old('role', $user->role) == 'petugas' ? 'selected' : '' }}>Petugas</option>
                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            @error('role')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">No. HP</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('no_hp')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.user.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Perbarui</button>
        </div>
    </form>
</div>
@endsection