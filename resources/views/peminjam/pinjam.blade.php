@extends('layouts.app')

@section('title', 'Form Peminjaman Alat')
@section('header-title', 'Form Peminjaman Alat')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
        
        <!-- Header Card -->
        <div class="bg-blue-600 px-6 py-4 text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h2 class="text-lg font-semibold">Form Peminjaman Alat (Multi-Item)</h2>
        </div>

        <div class="p-6 space-y-6">
            {{-- Alert Error --}}
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 text-red-700 rounded-r shadow-sm">
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Alert Success --}}
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 text-green-700 rounded-r shadow-sm">
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Form Peminjaman --}}
            <form action="{{ route('peminjam.pinjam.proses') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Daftar Alat yang Dipilih -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alat yang Dipilih:</label>
                    <div class="space-y-3">
                        @foreach($alats as $index => $item)
                            <input type="hidden" name="alat_id[]" value="{{ $item->id }}">
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-base font-bold text-gray-800">{{ $item->nama_alat }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mt-1">
                                        Stok Tersedia: {{ $item->stok }} Unit
                                    </span>
                                </div>

                                <div class="w-full sm:w-40">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Pinjam <span class="text-red-500">*</span></label>
                                    <input type="number" 
                                           name="jumlah[]" 
                                           min="1" 
                                           max="{{ $item->stok }}" 
                                           value="{{ old('jumlah.'.$index, 1) }}" 
                                           required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border text-sm text-gray-900">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Input Tanggal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="tanggal_pinjam" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Pinjam <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="tanggal_pinjam" 
                               id="tanggal_pinjam" 
                               value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" 
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2.5 border text-sm text-gray-900">
                    </div>

                    <div>
                        <label for="tgl_kembali_plan" class="block text-sm font-medium text-gray-700 mb-1">
                            Rencana Tanggal Kembali <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="tgl_kembali_plan" 
                               id="tgl_kembali_plan" 
                               value="{{ old('tgl_kembali_plan') }}" 
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2.5 border text-sm text-gray-900">
                    </div>
                </div>

                <!-- Input Alasan -->
                <div>
                    <label for="alasan" class="block text-sm font-medium text-gray-700 mb-1">
                        Alasan / Keperluan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="alasan" 
                              id="alasan" 
                              rows="3" 
                              placeholder="Jelaskan tujuan peminjaman alat..."
                              required
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2.5 border text-sm text-gray-900">{{ old('alasan') }}</textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <a href="{{ route('peminjam.katalog') }}" 
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium shadow-sm transition">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection