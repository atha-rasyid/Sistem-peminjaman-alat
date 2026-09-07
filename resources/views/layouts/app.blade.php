<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <!-- Memuat Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex">
            <!-- HEADER SIDEBAR DINAMIS -->
            <div class="p-5 text-xl font-bold tracking-wider border-b border-gray-800">
                PANEL {{ strtoupper(auth()->user()->role) }}
            </div>

            <nav class="flex-1 p-4 space-y-2">
                
                <!-- MENU DASHBOARD DINAMIS -->
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" 
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                       Dashboard & Log
                    </a>
                @elseif(auth()->user()->role === 'petugas')
                    @if(Route::has('petugas.dashboard'))
                        <a href="{{ route('petugas.dashboard') }}" 
                           class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('petugas.dashboard') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                           Dashboard Petugas
                        </a>
                    @endif
                @elseif(auth()->user()->role === 'peminjam')
                    @if(Route::has('peminjam.dashboard'))
                        <a href="{{ route('peminjam.dashboard') }}" 
                           class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('peminjam.dashboard') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                           Dashboard Peminjam
                        </a>
                    @endif
                @endif

                <!-- MENU KHUSUS ADMIN -->
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.alat.index') }}" 
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.alat*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                       Kelola Alat
                    </a>
                    <a href="{{ route('admin.kategori.index') }}" 
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.kategori*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                       Kelola Kategori
                    </a>
                    <a href="{{ route('admin.user.index') }}" 
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.user*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                       Kelola User
                    </a>
                @endif

                <!-- MENU KHUSUS PEMINJAM -->
                @if(auth()->user()->role === 'peminjam')
                    <div class="pt-2 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Layanan</div>
                    
                    @if(Route::has('peminjam.katalog'))
                        <a href="{{ route('peminjam.katalog') }}" 
                           class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('peminjam.katalog*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                           Katalog Alat
                        </a>
                    @endif

                    @if(Route::has('peminjam.riwayat'))
                        <a href="{{ route('peminjam.riwayat') }}" 
                           class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('peminjam.riwayat*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                           Riwayat Peminjaman
                        </a>
                    @endif
                @endif

                <!-- MENU TRANSAKSI DINAMIS (KHUSUS ADMIN & PETUGAS) -->
                @if(in_array(auth()->user()->role, ['admin', 'petugas']))
                    <div class="pt-2 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaksi</div>

                    <!-- 1. Persetujuan Peminjaman -->
                    @php
                        $peminjamanRoute = auth()->user()->role === 'admin' 
                            ? (Route::has('admin.peminjaman.index') ? route('admin.peminjaman.index') : '#')
                            : (Route::has('petugas.peminjaman.index') ? route('petugas.peminjaman.index') : '#');
                    @endphp
                    <a href="{{ $peminjamanRoute }}" 
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('*peminjaman*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                       Persetujuan Peminjaman
                    </a>

                    <!-- 2. Pemantauan Pengembalian -->
                    @php
                        $pengembalianRoute = auth()->user()->role === 'admin' 
                            ? (Route::has('admin.pengembalian.index') ? route('admin.pengembalian.index') : '#')
                            : (Route::has('petugas.pengembalian.index') ? route('petugas.pengembalian.index') : '#');
                    @endphp
                    <a href="{{ $pengembalianRoute }}" 
                       class="block px-4 py-2 rounded-lg transition whitespace-nowrap {{ request()->routeIs('*pengembalian*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                       Pemantauan Pengembalian
                    </a>
                @endif

                <!-- FITUR LAPORAN (KHUSUS PETUGAS) -->
                @if(auth()->user()->role === 'petugas')
                    <div class="pt-2 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Laporan</div>
                    @php
                        $laporanRoute = Route::has('petugas.laporan.index') ? route('petugas.laporan.index') : '#';
                    @endphp
                    <a href="{{ $laporanRoute }}" 
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('*laporan*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                       Laporan Transaksi
                    </a>
                @endif

            </nav>

            <div class="p-4 border-t border-gray-800 text-sm text-gray-400">
                Logged in as: <span class="text-white font-semibold">{{ auth()->user()->name }}</span>
            </div>
        </aside>

        <!-- MAIN CONTENT CONTAINER -->
        <div class="flex-1 flex flex-col overflow-y-auto">

            <!-- NAVBAR ATAS -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-10">
                <div class="text-lg font-semibold text-gray-800">
                    @yield('header-title', 'Dashboard')
                </div>
                <div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- KONTEN UTAMA HALAMAN -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>