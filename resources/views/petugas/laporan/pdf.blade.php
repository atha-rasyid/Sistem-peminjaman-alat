<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman {{ $bulan }}-{{ $tahun }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .summary { margin-top: 15px; width: 100%; }
        .summary td { border: none; padding: 4px 0; }
    </style>
</head>
<body onload="window.print()"> <!-- window.print() berguna jika dibuka tanpa DomPDF -->
    <div class="header">
        <h2>LAPORAN TRANSAKSI PEMINJAMAN ALAT</h2>
        <p>Periode: {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}</p>
    </div>

    <table class="summary">
        <tr>
            <td><strong>Total Transaksi:</strong> {{ $totalPeminjaman }}</td>
            <td style="text-align: right;"><strong>Total Denda:</strong> Rp {{ number_format($totalDendaBulanIni, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Peminjam</th>
                <th>Tanggal</th>
                <th>Barang (Jumlah)</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peminjamans as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->user->name ?? 'User Terhapus' }}</td>
                    <td>{{ optional($item->created_at)->format('d/m/Y') }}</td>
                    <td>
                        @foreach($item->detailPinjams as $detail)
                            - {{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }})<br>
                        @endforeach
                    </td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>Rp {{ number_format($item->total_denda ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data laporan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>