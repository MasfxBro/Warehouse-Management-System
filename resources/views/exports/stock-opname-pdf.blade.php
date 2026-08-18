<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stock Opname (Audit Fisik)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
        .text-green { color: #276749; font-weight: bold; }
        .text-red { color: #c53030; font-weight: bold; }
        .text-blue { color: #2b6cb0; font-weight: bold; }
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
        <h2>Laporan Stock Opname (Audit Fisik Inventory)</h2>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }} | System: WMS Inventory Control</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 12%;">SKU</th>
                <th style="width: 22%;">Nama Barang</th>
                <th class="text-right" style="width: 10%;">Stok Sistem</th>
                <th class="text-right" style="width: 10%;">Stok Fisik</th>
                <th class="text-right" style="width: 10%;">Selisih</th>
                <th class="text-center" style="width: 10%;">Status</th>
                <th style="width: 12%;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opnames as $index => $op)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($op->tanggal_opname)->format('Y-m-d') }}</td>
                <td class="font-mono">{{ $op->SKU }}</td>
                <td>{{ $op->masterBarang->Nama ?? '-' }}</td>
                <td class="text-right">{{ number_format($op->stok_sistem) }}</td>
                <td class="text-right">{{ number_format($op->stok_fisik) }}</td>
                <td class="text-right">
                    @if($op->variance > 0)
                    <span class="text-blue">+{{ number_format($op->variance) }}</span>
                    @elseif($op->variance < 0)
                    <span class="text-red">{{ number_format($op->variance) }}</span>
                    @else
                    0
                    @endif
                </td>
                <td class="text-center">
                    @if($op->variance == 0)
                    <span class="text-green">SESUAI</span>
                    @elseif($op->variance < 0)
                    <span class="text-red">KURANG</span>
                    @else
                    <span class="text-blue">LEBIH</span>
                    @endif
                </td>
                <td>{{ $op->user->name ?? 'System' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak secara otomatis oleh Warehouse Management System (WMS)</p>
    </div>
</body>
</html>
