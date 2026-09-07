@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')
    <!-- Alert Selamat Datang -->
    <div class="mb-6 bg-emerald-100 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm">
        Selamat datang, <strong class="font-semibold">{{ auth()->user()->name }}</strong>! Anda login sebagai hak akses
        <span class="uppercase font-bold text-emerald-900">{{ auth()->user()->role }}</span>.
    </div>

    <!-- Tabel Log Aktivitas -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Log Aktivitas Terbaru</h3>
            <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-semibold bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span>
                Live Updates
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Waktu</th>
                        <th class="py-3 px-4 border-b">User</th>
                        <th class="py-3 px-4 border-b">Aktivitas</th>
                    </tr>
                </thead>
                <tbody id="log-table-body" class="text-gray-700 text-sm">
                    {{-- Load Pertama kali via Blade --}}
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                            <td class="py-3 px-4 text-xs text-gray-500">
                                {{ $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i:s') : '-' }}
                            </td>
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $log->user->name ?? 'Sistem' }}</td>
                            <td class="py-3 px-4">{{ $log->aktivitas }}</td>
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

    <!-- Auto Refresh Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

                        let html = '';
                        data.forEach(log => {
                            html += `
                                <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                                    <td class="py-3 px-4 text-xs text-gray-500">${log.waktu ?? '-'}</td>
                                    <td class="py-3 px-4 font-medium text-gray-900">${log.user_name ?? 'Sistem'}</td>
                                    <td class="py-3 px-4">${log.aktivitas}</td>
                                </tr>
                            `;
                        });

                        tbody.innerHTML = html;
                    })
                    .catch(err => console.error("Gagal mengambil log real-time:", err));
            }

            // Memperbarui log setiap 3 detik
            setInterval(updateLogs, 3000);
        });
    </script>
@endsection