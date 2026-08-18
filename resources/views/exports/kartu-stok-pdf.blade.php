<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kartu Stok / Stock Ledger</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .badge-reorder { color: #c53030; font-weight: bold; }
        .badge-aman { color: #276749; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Kartu Stok / Stock Ledger</h2>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }} | System: WMS Inventory Control</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 15%;">SKU</th>
                <th style="width: 30%;">Nama Barang</th>
                <th class="text-right" style="width: 10%;">Stok Awal</th>
                <th class="text-right" style="width: 10%;">Total Masuk</th>
                <th class="text-right" style="width: 10%;">Total Keluar</th>
                <th class="text-right" style="width: 10%;">Stok Akhir</th>
                <th class="text-right" style="width: 10%;">Min. Stok</th>
                <th class="text-center" style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($barangs as $index => $b)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono">{{ $b['sku'] }}</td>
                <td>{{ $b['nama'] }}</td>
                <td class="text-right">{{ number_format($b['stok_awal']) }}</td>
                <td class="text-right">+{{ number_format($b['total_masuk']) }}</td>
                <td class="text-right">-{{ number_format($b['total_keluar']) }}</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($b['stok_akhir']) }}</td>
                <td class="text-right">{{ number_format($b['min_stok']) }}</td>
                <td class="text-center">
                    @if($b['status'] === 'REORDER')
                    <span class="badge-reorder">REORDER</span>
                    @else
                    <span class="badge-aman">AMAN</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak secara otomatis oleh Warehouse Management System (WMS)</p>
    </div>
</body>
</html>
