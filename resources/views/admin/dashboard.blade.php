@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')
    @php
        $inisial = fn ($nama) => mb_strtoupper(mb_substr($nama ?: '?', 0, 1));

        // Warna avatar solid (urutan sama dengan di JavaScript)
        $paletAvatar = ['bg-blue-600', 'bg-violet-600'];
        $warnaAvatar = fn ($nama) => $paletAvatar[mb_ord($inisial($nama)) % count($paletAvatar)];

        $badgeRole = [
            'admin'    => 'bg-violet-100 text-violet-700 border-violet-200',
            'petugas'  => 'bg-cyan-100 text-cyan-700 border-cyan-200',
            'peminjam' => 'bg-blue-100 text-blue-700 border-blue-200',
        ];

        // Nilai fallback variabel
        $vAlatTersedia      = $alatTersedia ?? 0;
        $vTotalAlat         = $totalAlat ?? 0;
        $vJenisAlatTersedia = $jenisAlatTersedia ?? 0;
        $vTotalUser         = $totalUser ?? 0;
        $vTahun             = $tahun ?? date('Y');
        $vTotalPeminjaman   = $totalPeminjaman ?? 0;
        $vMenunggu          = $menunggu ?? 0;
        $vTotalPengembalian = $totalPengembalian ?? 0;
        $vDataPeminjaman    = $dataPeminjaman ?? array_fill(0, 12, 0);
        $vDataPengembalian  = $dataPengembalian ?? array_fill(0, 12, 0);
        $vAlatList          = $alatList ?? [];
        $vUsers             = $users ?? [];
        $vLogs              = $logs ?? [];
    @endphp

    {{-- Banner selamat datang --}}
    <div class="glass-card rounded-2xl p-6 mb-6">
        <p class="text-[10px] uppercase tracking-[0.2em] text-blue-600 font-semibold">Dashboard</p>
        <p class="brand text-2xl text-gray-900 font-bold mt-2">
            Selamat datang, {{ auth()->user()->name ?? 'User' }}
        </p>
        <p class="text-sm text-gray-500 mt-3">
            Anda login sebagai hak akses
            <span class="uppercase text-xs font-bold text-white bg-blue-600 px-2.5 py-1 rounded-lg ml-1">{{ auth()->user()->role ?? 'Guest' }}</span>
        </p>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-[3px] bg-blue-600"></div>
            <div class="flex items-center justify-between">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-semibold">Alat tersedia</p>
                <span class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center">🛠️</span>
            </div>
            <p class="text-4xl font-bold mt-3 text-blue-600">{{ $vAlatTersedia }}</p>
            <p class="text-xs text-gray-500 mt-2">{{ $vJenisAlatTersedia }} jenis, dari {{ $vTotalAlat }} unit</p>
        </div>
        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-[3px] bg-violet-600"></div>
            <div class="flex items-center justify-between">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-semibold">User terdaftar</p>
                <span class="w-9 h-9 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center">👥</span>
            </div>
            <p class="text-4xl font-bold mt-3 text-violet-600">{{ $vTotalUser }}</p>
            <p class="text-xs text-gray-500 mt-2">admin, petugas, peminjam</p>
        </div>
        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-[3px] bg-cyan-500"></div>
            <div class="flex items-center justify-between">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-semibold">Peminjaman {{ $vTahun }}</p>
                <span class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center">📤</span>
            </div>
            <p class="text-4xl font-bold mt-3 text-cyan-600">{{ $vTotalPeminjaman }}</p>
            <p class="text-xs text-gray-500 mt-2">{{ $vMenunggu }} menunggu persetujuan</p>
        </div>
        <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-[3px] bg-purple-600"></div>
            <div class="flex items-center justify-between">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-semibold">Pengembalian {{ $vTahun }}</p>
                <span class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center">📥</span>
            </div>
            <p class="text-4xl font-bold mt-3 text-purple-600">{{ $vTotalPengembalian }}</p>
            <p class="text-xs text-gray-500 mt-2">transaksi selesai</p>
        </div>
    </div>

    {{-- Diagram --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="lg:col-span-2 glass-card rounded-2xl p-5">
            <h3 class="text-[11px] uppercase tracking-[0.2em] text-gray-600 font-semibold mb-4">
                Peminjaman dan pengembalian per bulan ({{ $vTahun }})
            </h3>
            <div class="relative h-72">
                <canvas id="chartBulanan"></canvas>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <h3 class="text-[11px] uppercase tracking-[0.2em] text-gray-600 font-semibold mb-4">Status unit alat</h3>
            <div class="relative h-72">
                <canvas id="chartAlat"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- Alat tersedia --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex items-center gap-3">
                <span class="w-1.5 h-5 rounded-full bg-blue-600"></span>
                <h3 class="brand text-lg font-bold text-gray-900">Alat Tersedia</h3>
            </div>
            <ul class="divide-y divide-gray-100 text-sm">
                @forelse ($vAlatList as $a)
                    <li class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                        <div>
                            <p class="font-medium text-gray-900">{{ $a->nama_alat }}</p>
                            <p class="text-xs text-gray-500">{{ $a->kategori->nama_kategori ?? '-' }} &middot; {{ $a->status_kondisi }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-cyan-100 text-cyan-700 border border-cyan-200">
                            {{ $a->stok }} unit
                        </span>
                    </li>
                @empty
                    <li class="px-5 py-4 text-center text-gray-500">Tidak ada alat yang tersedia.</li>
                @endforelse
            </ul>
        </div>

        {{-- Daftar user --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex items-center gap-3">
                <span class="w-1.5 h-5 rounded-full bg-violet-600"></span>
                <h3 class="brand text-lg font-bold text-gray-900">User Terdaftar</h3>
            </div>
            <ul class="divide-y divide-gray-100 text-sm">
                @forelse ($vUsers as $u)
                    <li class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <span class="relative inline-flex w-9 h-9 shrink-0 items-center justify-center rounded-full {{ $warnaAvatar($u->name) }} text-white text-xs font-bold overflow-hidden">
                                {{ $inisial($u->name) }}
                                @if (!empty($u->foto))
                                    <img src="{{ asset('storage/' . $u->foto) }}" alt="" class="absolute inset-0 w-full h-full object-cover" onerror="this.remove()">
                                @endif
                            </span>
                            <div>
                                <p class="font-medium text-gray-900">{{ $u->name }}</p>
                                <p class="text-xs text-gray-500">{{ $u->email }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full border {{ $badgeRole[$u->role] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">{{ $u->role }}</span>
                    </li>
                @empty
                    <li class="px-5 py-4 text-center text-gray-500">Belum ada user.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Log aktivitas --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-5 rounded-full bg-cyan-500"></span>
                <h3 class="brand text-lg font-bold text-gray-900">Log Aktivitas Terbaru</h3>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-cyan-700 bg-cyan-50 px-2.5 py-1 rounded-full border border-cyan-200">
                <span class="w-2 h-2 bg-cyan-500 rounded-full animate-ping"></span>
                Live Updates
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-[0.2em]">
                        <th class="py-3 px-5 border-b border-gray-200">Waktu</th>
                        <th class="py-3 px-5 border-b border-gray-200">User</th>
                        <th class="py-3 px-5 border-b border-gray-200">Aktivitas</th>
                    </tr>
                </thead>
                <tbody id="log-table-body" class="text-gray-700 text-sm">
                    @forelse ($vLogs as $log)
                        @php $namaLog = $log->user->name ?? 'Sistem'; @endphp
                        <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                            <td class="py-3 px-5 text-xs text-gray-500">
                                {{ $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i:s') : '-' }}
                            </td>
                            <td class="py-3 px-5 font-medium text-gray-900">
                                <span class="inline-flex items-center gap-2">
                                    <span class="relative inline-flex w-7 h-7 shrink-0 items-center justify-center rounded-full {{ $warnaAvatar($namaLog) }} text-white text-[10px] font-bold overflow-hidden">
                                        {{ $inisial($namaLog) }}
                                        @if (!empty($log->user) && !empty($log->user->foto))
                                            <img src="{{ asset('storage/' . $log->user->foto) }}" alt="" class="absolute inset-0 w-full h-full object-cover" onerror="this.remove()">
                                        @endif
                                    </span>
                                    {{ $namaLog }}
                                </span>
                            </td>
                            <td class="py-3 px-5">{{ $log->aktivitas }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">Belum ada log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.color = '#6b7280';
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.borderColor = '#e5e7eb';

            new Chart(document.getElementById('chartBulanan'), {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                    datasets: [
                        { label: 'Peminjaman', data: @json($vDataPeminjaman), backgroundColor: '#2563eb', hoverBackgroundColor: '#1d4ed8', borderRadius: 6 },
                        { label: 'Pengembalian', data: @json($vDataPengembalian), backgroundColor: '#06b6d4', hoverBackgroundColor: '#0891b2', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            new Chart(document.getElementById('chartAlat'), {
                type: 'doughnut',
                data: {
                    labels: ['Tersedia', 'Sedang dipinjam'],
                    datasets: [{
                        data: [{{ $vAlatTersedia }}, {{ max($vTotalAlat - $vAlatTersedia, 0) }}],
                        backgroundColor: ['#06b6d4', '#7c3aed'],
                        borderWidth: 3,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            const palet = ['bg-blue-600', 'bg-violet-600'];

            function avatar(nama, foto) {
                const huruf = (nama || '?').charAt(0).toUpperCase();
                const warna = palet[huruf.charCodeAt(0) % palet.length];
                const img = foto ? `<img src="${esc(foto)}" alt="" class="absolute inset-0 w-full h-full object-cover" onerror="this.remove()">` : '';
                return `<span class="relative inline-flex w-7 h-7 shrink-0 items-center justify-center rounded-full ${warna} text-white text-[10px] font-bold overflow-hidden">${esc(huruf)}${img}</span>`;
            }

            function updateLogs() {
                fetch("{{ route('admin.logs.fetch') }}")
                    .then(response => {
                        if (!response.ok) throw new Error("HTTP error " + response.status);
                        return response.json();
                    })
                    .then(data => {
                        const tbody = document.getElementById('log-table-body');
                        if (!data || data.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="3" class="py-4 text-center text-gray-500">Belum ada log aktivitas.</td></tr>`;
                            return;
                        }
                        tbody.innerHTML = data.map(log => `
                            <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                                <td class="py-3 px-5 text-xs text-gray-500">${esc(log.waktu ?? '-')}</td>
                                <td class="py-3 px-5 font-medium text-gray-900">
                                    <span class="inline-flex items-center gap-2">${avatar(log.user_name, log.user_foto)}${esc(log.user_name ?? 'Sistem')}</span>
                                </td>
                                <td class="py-3 px-5">${esc(log.aktivitas)}</td>
                            </tr>
                        `).join('');
                    })
                    .catch(err => console.error("Gagal mengambil log real-time:", err));
            }

            setInterval(updateLogs, 3000);
        });
    </script>
@endsection