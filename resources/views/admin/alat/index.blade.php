@extends('layouts.app')

@section('title', 'Kelola Alat - Panel Admin')
@section('header-title', 'Manajemen Inventaris Alat')

@section('content')
@if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
        {{ session('error') }}
    </div>
@endif

<!-- Informasi Denda Keterlambatan Global -->
<div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg shadow-sm text-sm flex items-center justify-between">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span><strong>Ketentuan Denda Keterlambatan:</strong> Seluruh peminjaman alat yang terlambat dikenakan denda sebesar <strong>Rp 10.000 per 3 hari</strong>.</span>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
        <h3 class="text-lg font-bold text-gray-800">Daftar Inventaris Alat</h3>
        
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form action="{{ route('admin.alat.index') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari alat, kategori, kondisi..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.alat.index') }}" 
                       class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition" title="Reset Pencarian">
                        Reset
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.alat.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">
                + Tambah Alat
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <th class="py-3 px-4 border-b w-16">No</th>
                    <th class="py-3 px-4 border-b w-20">Foto</th>
                    <th class="py-3 px-4 border-b">Nama Alat</th>
                    <th class="py-3 px-4 border-b">Kategori</th>
                    <th class="py-3 px-4 border-b">Stok</th>
                    <th class="py-3 px-4 border-b">Status / Kondisi</th>
                    <th class="py-3 px-4 border-b">Deskripsi</th>
                    <th class="py-3 px-4 border-b w-36">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                @forelse($alats as $index => $alat)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4 border-b text-gray-500">{{ $alats->firstItem() + $index }}</td>
                        
                        <!-- Kolom Foto Alat -->
                        <td class="py-3 px-4 border-b">
                            @if($alat->gambar)
                                <img src="{{ asset('storage/' . $alat->gambar) }}" alt="{{ $alat->nama_alat }}" 
                                     class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            @else
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200 text-gray-400 text-xs font-semibold">
                                    No Pic
                                </div>
                            @endif
                        </td>

                        <td class="py-3 px-4 border-b font-medium text-gray-900">{{ $alat->nama_alat }}</td>
                        <td class="py-3 px-4 border-b">
                            <span class="bg-gray-100 text-gray-800 text-xs px-2.5 py-1 rounded-full font-semibold">
                                {{ $alat->kategori->nama_kategori ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 border-b font-semibold text-gray-800">{{ $alat->stok }}</td>
                        <td class="py-3 px-4 border-b">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                                @if(strtolower($alat->status_kondisi) == 'baik') bg-emerald-100 text-emerald-800 
                                @elseif(strtolower($alat->status_kondisi) == 'rusak ringan') bg-amber-100 text-amber-800 
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($alat->status_kondisi) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 border-b text-gray-600">
                            @if(!empty($alat->deskripsi))
                                {{ Str::limit($alat->deskripsi, 30) }}
                            @else
                                <span class="text-gray-400 italic">Tidak ada deskripsi</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 border-b">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.alat.edit', $alat->id) }}" 
                                   class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                    Edit
                                </a>

                                <form action="{{ route('admin.alat.destroy', $alat->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus alat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-4 text-center text-gray-500">Belum ada data alat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200 bg-gray-50">
        {{ $alats->links() }}
    </div>
</div>
@endsection