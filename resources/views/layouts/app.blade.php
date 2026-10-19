<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Peminjaman')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .brand { font-family: 'Inter', sans-serif; letter-spacing: -0.01em; }
        .btn-grad { background-image: linear-gradient(to right, #2563eb, #22d3ee); }
        .glass-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-100 text-gray-800 antialiased">
@php
    $user    = auth()->user();
    $role    = $user->role ?? 'guest';
    $current = request()->route() ? request()->route()->getName() : '';

    $menus = match ($role) {
        'admin' => [
            ['admin.dashboard',          '🏠', 'Dashboard'],
            ['admin.alat.index',         '🛠️', 'Alat'],
            ['admin.kategori.index',     '🏷️', 'Kategori'],
            ['admin.user.index',         '👥', 'User'],
            ['admin.peminjaman.index',   '📤', 'Peminjaman'],
            ['admin.pengembalian.index', '📥', 'Pengembalian'],
        ],
        'petugas' => [
            ['petugas.peminjaman.index',   '📤', 'Peminjaman'],
            ['petugas.pengembalian.index', '📥', 'Pengembalian'],
            ['petugas.laporan.index',      '📊', 'Laporan'],
        ],
        'peminjam' => [
            ['peminjam.dashboard', '🏠', 'Dashboard'],
            ['peminjam.katalog',   '🛠️', 'Katalog Alat'],
            ['peminjam.pinjam',    '📝', 'Ajukan Peminjaman'],
            ['peminjam.riwayat',   '📚', 'Riwayat Peminjaman'],
        ],
        default => [],
    };
@endphp

<div class="min-h-screen flex">
    {{-- Sidebar --}}
    <aside class="hidden md:flex w-64 shrink-0 flex-col bg-slate-900 text-gray-200">
        <div class="px-6 py-5 border-b border-white/10">
            <p class="brand text-lg font-bold text-white">Sistem Peminjaman</p>
            <p class="text-xs text-gray-400 mt-0.5 uppercase tracking-wider">{{ $role }}</p>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            @foreach ($menus as [$routeName, $ikon, $label])
                @if (Route::has($routeName))
                    <a href="{{ route($routeName) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                              {{ str_starts_with($current, rtrim(str_replace('.index', '', $routeName), '.')) ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/5' }}">
                        <span>{{ $ikon }}</span>{{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="px-4 py-4 border-t border-white/10">
            <p class="text-sm font-medium text-white truncate">{{ $user->name ?? 'User' }}</p>
            <p class="text-xs text-gray-400 truncate">{{ $user->email ?? '' }}</p>
            @if (Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm px-3 py-2 rounded-lg bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 transition">
                        Keluar
                    </button>
                </form>
            @endif
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <h1 class="text-xl font-bold text-gray-800">@yield('header-title', 'Dashboard')</h1>
        </header>

        <main class="flex-1 p-6">
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>