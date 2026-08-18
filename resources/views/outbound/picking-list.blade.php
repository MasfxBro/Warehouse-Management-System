<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking List - {{ $outbound->No_Shipping }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0 0 10px 0;
        }
        .header p {
            color: #666;
            margin: 5px 0;
        }
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #333;
            font-size: 16px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 180px;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .picking-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .picking-table th {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .picking-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .picking-table tr:hover {
            background-color: #f5f5f5;
        }
        .picking-table tr:last-child td {
            border-bottom: 2px solid #4CAF50;
        }
        .qty-highlight {
            background-color: #fff3cd;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            color: #856404;
        }
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .buttons {
            margin-top: 30px;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        .button-primary {
            background-color: #4CAF50;
            color: white;
        }
        .button-primary:hover {
            background-color: #45a049;
        }
        .button-secondary {
            background-color: #6c757d;
            color: white;
        }
        .button-secondary:hover {
            background-color: #5a6268;
        }
        .note {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .note strong {
            color: #856404;
        }
        @media print {
            .buttons {
                display: none;
            }
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="header">
        <h1>📋 PICKING LIST</h1>
        <p>Daftar Pengambilan Barang (FIFO)</p>
    </div>

    @if(session('success'))
        <div class="success-message">
            <strong>✓ Berhasil!</strong> {{ session('success') }}
        </div>
    @endif

    <div class="info-section">
        <h3>Informasi Pengiriman</h3>
        <div class="info-row">
            <div class="info-label">No. Shipping:</div>
            <div class="info-value">{{ $outbound->No_Shipping }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Pengiriman:</div>
            <div class="info-value">{{ $outbound->Tanggal->format('d M Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Customer:</div>
            <div class="info-value">{{ $outbound->customer->Nama }}</div>
        </div>
        @if($outbound->No_Surat_Jalan)
        <div class="info-row">
            <div class="info-label">No. Surat Jalan:</div>
            <div class="info-value">{{ $outbound->No_Surat_Jalan }}</div>
        </div>
        @endif
    </div>

    <h3>📦 Daftar Barang yang Harus Diambil</h3>

    @if(count($pickingList) > 0)
        <table class="picking-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Rak</th>
                    <th>Batch</th>
                    <th>Qty Pick</th>
                    <th>Tanggal Inbound</th>
                    <th>No. Receiving</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQty = 0;
                @endphp
                @foreach($pickingList as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item['sku'] }}</strong></td>
                        <td>{{ $item['nama_barang'] }}</td>
                        <td><strong>{{ $item['kode_rak'] }}</strong></td>
                        <td>{{ $item['batch'] }}</td>
                        <td><span class="qty-highlight">{{ $item['qty_pick'] }} unit</span></td>
                        <td>{{ $item['tanggal_inbound'] }}</td>
                        <td>{{ $item['no_receiving'] }}</td>
                    </tr>
                    @php
                        $totalQty += $item['qty_pick'];
                    @endphp
                @endforeach
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">TOTAL:</td>
                    <td><span class="qty-highlight">{{ $totalQty }} unit</span></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <div class="note">
            <strong>📌 Catatan FIFO:</strong><br>
            Barang diambil berdasarkan urutan tanggal penerimaan (First In First Out).<br>
            Ambil barang dari rak yang tercantum sesuai urutan di atas.
        </div>
    @else
        <p style="text-align: center; color: #999; padding: 20px;">
            Tidak ada picking list yang tersedia.
        </p>
    @endif

    <div class="buttons">
        <a href="{{ route('outbound.surat-jalan', $outbound->Outbound_ID) }}" class="button button-primary" style="background-color: #007bff;">
            📄 Download Surat Jalan PDF
        </a>
        <button onclick="window.print()" class="button button-primary">
            🖨️ Cetak Picking List
        </button>
        <a href="{{ route('outbound.index') }}" class="button button-secondary">
            ← Kembali ke Daftar Outbound
        </a>
    </div>

</div>

</body>
</html>
