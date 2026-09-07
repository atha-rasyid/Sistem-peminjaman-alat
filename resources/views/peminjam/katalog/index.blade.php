@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="container mx-auto px-4 py-8">
    
    <!-- 1. Header & Quick Stats -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Daftar Alat Tersedia</h1>
            <p class="text-gray-500 text-sm mt-1">Pilih dan ajukan peminjaman alat dengan mudah</p>
        </div>
        
        <!-- Summary Badges -->
        <div class="flex items-center gap-3">
            <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-2 text-center">
                <span class="block text-xs text-blue-600 font-medium">Total Jenis Alat</span>
                <span class="text-lg font-bold text-blue-800">{{ $alats->count() }}</span>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-2 text-center">
                <span class="block text-xs text-emerald-600 font-medium">Siap Dipinjam</span>
                <span class="text-lg font-bold text-emerald-800">{{ $alats->sum('stok') }} Unit</span>
            </div>
        </div>
    </div>

    <!-- 2. Filter & Search Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-8">
        <form action="{{ route('peminjam.katalog') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <!-- Search Field -->
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Cari nama alat..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Category Filter -->
            <div class="w-full md:w-64">
                <select name="kategori" onchange="this.form.submit()" 
                    class="w-full py-2.5 px-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-600">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- 3. Grid Katalog Alat -->
    @if($alats->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center border border-gray-100">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700">Alat Tidak Ditemukan</h3>
            <p class="text-gray-400 text-sm">Coba ubah kata kunci atau pilih kategori lain.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($alats as $item)
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden flex flex-col justify-between">
                    <div>
                        <!-- Gambar & Status Badge -->
                        <div class="relative bg-gray-50 aspect-video w-full overflow-hidden">
                            <img src="{{ $item->gambar ? asset('storage/'.$item->gambar) : asset('images/default-item.png') }}" 
                                 alt="{{ $item->nama_alat }}" 
                                 class="w-full h-full object-cover">

                            <!-- Kategori Tag -->
                            <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-md text-gray-700 text-xs font-medium px-2.5 py-1 rounded-lg border border-gray-200">
                                {{ $item->kategori->nama_kategori ?? 'Umum' }}
                            </span>

                            <!-- Status Stok Badge -->
                            @if($item->stok > 0)
                                <span class="absolute top-3 right-3 bg-emerald-500/90 text-white text-xs font-semibold px-2.5 py-1 rounded-lg shadow-sm">
                                    Tersedia
                                </span>
                            @else
                                <span class="absolute top-3 right-3 bg-rose-500/90 text-white text-xs font-semibold px-2.5 py-1 rounded-lg shadow-sm">
                                    Habis
                                </span>
                            @endif
                        </div>

                        <!-- Info Detail -->
                        <div class="p-5">
                            <h2 class="text-lg font-bold text-gray-800 line-clamp-1 mb-1" title="{{ $item->nama_alat }}">
                                {{ $item->nama_alat }}
                            </h2>
                            <p class="text-xs text-gray-500 line-clamp-2 mb-4">
                                {{ $item->deskripsi ?? 'Tidak ada deskripsi singkat.' }}
                            </p>

                            <!-- Stok Counter -->
                            <div class="flex items-center justify-between text-sm py-2 border-t border-gray-100">
                                <span class="text-gray-500">Stok Tersedia:</span>
                                <span class="font-bold {{ $item->stok > 0 ? 'text-gray-800' : 'text-rose-500' }}">
                                    {{ $item->stok }} Unit
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="p-5 pt-0">
                        @if($item->stok > 0)
                            <a href="{{ route('peminjam.pinjam', $item->id) }}" 
                               class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors shadow-sm">
                                Pinjam Alat
                            </a>
                        @else
                            <button disabled 
                                    class="w-full bg-gray-100 text-gray-400 text-sm font-semibold py-2.5 rounded-xl cursor-not-allowed">
                                Tidak Tersedia
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $alats->links() }}
        </div>
    @endif

</div>
@endsection