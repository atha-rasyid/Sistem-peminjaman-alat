@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="container mx-auto px-4 py-8">
    
    <!-- 1. Header & Quick Stats (Siap Dipinjam dihilangkan) -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Daftar Alat Tersedia</h1>
            <p class="text-gray-500 text-sm mt-1">Pilih satu atau beberapa alat lalu ajukan peminjaman dengan mudah</p>
        </div>
        
        <!-- Summary Badge (Hanya Total Jenis Alat) -->
        <div class="flex items-center gap-3">
            <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-2 text-center">
                <span class="block text-xs text-blue-600 font-medium">Total Jenis Alat</span>
                <span class="text-lg font-bold text-blue-800">{{ $alat->count() }}</span>
            </div>
        </div>
    </div>

    <!-- 2. Filter, Search Bar & Button Pinjam Alat di Jajaran Search Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-8">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <form action="{{ route('peminjam.katalog') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1 w-full">
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

            <!-- Tombol Pinjam Alat di Jajaran Search Bar (Disabled sebelum pilih barang) -->
            <div>
                <button type="button" id="btn-pinjam-aksi" disabled
                    class="w-full md:w-auto px-6 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm bg-gray-200 text-gray-400 cursor-not-allowed">
                    Pinjam Alat (<span id="selected-count">0</span>)
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Grid Katalog Alat -->
    @if($alat->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center border border-gray-100">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700">Alat Tidak Ditemukan</h3>
            <p class="text-gray-400 text-sm">Coba ubah kata kunci atau pilih kategori lain.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($alat as $item)
                @if($item->stok > 0)
                    <!-- Card Alat yang Bisa Dipilih Multi (clickable) -->
                    <div onclick="togglePilihAlat(this, '{{ $item->id }}')" 
                         class="alat-card bg-white rounded-2xl shadow-sm hover:shadow-md transition-all border-2 border-transparent cursor-pointer overflow-hidden flex flex-col justify-between relative group">
                @else
                    <!-- Card Alat Habis -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 opacity-60 cursor-not-allowed overflow-hidden flex flex-col justify-between">
                @endif
                    <div>
                        <!-- Gambar & Status Badge -->
                        <div class="relative bg-gray-50 aspect-video w-full overflow-hidden">
                            <img src="{{ $item->gambar ? asset('storage/'.$item->gambar) : asset('images/default-item.png') }}" 
                                 alt="{{ $item->nama_alat }}" 
                                 class="alat-img w-full h-full object-cover transition-all duration-300">

                            <!-- Checkmark Badge (Muncul saat dipilih) -->
                            <div class="check-badge absolute inset-0 bg-blue-900/30 backdrop-blur-[2px] flex items-center justify-center opacity-0 transition-opacity">
                                <span class="bg-blue-600 text-white p-2 rounded-full shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </div>

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
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $alat->links() }}
        </div>
    @endif

</div>

<!-- Script untuk Multi-Select Alat & Navigasi -->
<script>
    let selectedAlatIds = [];

    function togglePilihAlat(element, id) {
        const img = element.querySelector('.alat-img');
        const badge = element.querySelector('.check-badge');

        const index = selectedAlatIds.indexOf(id);

        if (index > -1) {
            // Jika sudah dipilih, batalkan pilihan (deselect)
            selectedAlatIds.splice(index, 1);
            element.classList.remove('border-blue-600', 'ring-2', 'ring-blue-400');
            if (img) img.classList.remove('brightness-50', 'scale-105');
            if (badge) badge.classList.remove('opacity-100');
        } else {
            // Jika belum dipilih, tambahkan ke daftar (select)
            selectedAlatIds.push(id);
            element.classList.add('border-blue-600', 'ring-2', 'ring-blue-400');
            if (img) img.classList.add('brightness-50', 'scale-105');
            if (badge) badge.classList.add('opacity-100');
        }

        // Update Counter & Status Tombol di Search Bar
        const btnPinjam = document.getElementById('btn-pinjam-aksi');
        const countSpan = document.getElementById('selected-count');
        countSpan.textContent = selectedAlatIds.length;

        if (selectedAlatIds.length > 0) {
            btnPinjam.removeAttribute('disabled');
            btnPinjam.classList.remove('bg-gray-200', 'text-gray-400', 'cursor-not-allowed');
            btnPinjam.classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white', 'cursor-pointer');
        } else {
            btnPinjam.setAttribute('disabled', 'true');
            btnPinjam.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'text-white', 'cursor-pointer');
            btnPinjam.classList.add('bg-gray-200', 'text-gray-400', 'cursor-not-allowed');
        }
    }

    // Ketika tombol 'Pinjam Alat' di search bar diklik, kirim array ID alat yang dipilih ke form peminjaman
    document.getElementById('btn-pinjam-aksi').addEventListener('click', function() {
        if (selectedAlatIds.length > 0) {
            // Contoh mengirim parameter ID dengan format query string (misal: ?ids=1,2,3) atau sesuaikan route Anda
            window.location.href = "{{ url('peminjam/pinjam') }}?alat_ids=" + selectedAlatIds.join(',');
        }
    });
</script>
@endsection