<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengiriman Outbound</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .header-actions {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-primary:hover {
            background-color: #45a049;
        }
        .btn-info {
            background-color: #2196F3;
            color: white;
            font-size: 13px;
            padding: 6px 12px;
        }
        .btn-info:hover {
            background-color: #0b7dda;
        }
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>

<div class="container">
    
    <h1>📦 Daftar Pengiriman Barang (Outbound)</h1>

    @if(session('success'))
        <div class="success-message">
            <strong>✓ Berhasil!</strong> {{ session('success') }}
        </div>
    @endif

    <div class="header-actions">
        <a href="{{ route('outbound.create') }}" class="btn btn-primary">
            + Tambah Pengiriman Baru
        </a>
    </div>

    <hr>

    @if($outbounds->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Shipping</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>No. Surat Jalan</th>
                    <th>SKU / Barang</th>
                    <th>Qty</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($outbounds as $index => $outbound)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $outbound->No_Shipping }}</strong></td>
                        <td>{{ $outbound->Tanggal->format('d M Y') }}</td>
                        <td>{{ $outbound->customer->Nama }}</td>
                        <td>{{ $outbound->No_Surat_Jalan ?? '-' }}</td>
                        <td>
                            @foreach($outbound->outboundDetails as $detail)
                                <div>
                                    <strong>{{ $detail->SKU }}</strong> - {{ $detail->masterBarang->Nama }}
                                </div>
                            @endforeach
                        </td>
                        <td>
                            @foreach($outbound->outboundDetails as $detail)
                                <div>
                                    <span class="badge badge-success">{{ $detail->Qty }} unit</span>
                                </div>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('outbound.picking-list', $outbound->Outbound_ID) }}" class="btn btn-info" style="margin-bottom: 4px; display: inline-block;">
                                📋 Picking List
                            </a>
                            <a href="{{ route('outbound.surat-jalan', $outbound->Outbound_ID) }}" class="btn btn-info" style="background-color: #e67e22;">
                                📄 Surat Jalan
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 20px; color: #666; font-size: 14px;">
            Total: <strong>{{ $outbounds->count() }}</strong> transaksi outbound
        </p>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h3>Belum Ada Transaksi Outbound</h3>
            <p>Klik tombol "Tambah Pengiriman Baru" untuk membuat transaksi pertama.</p>
        </div>
    @endif

</div>

</body>
</html>