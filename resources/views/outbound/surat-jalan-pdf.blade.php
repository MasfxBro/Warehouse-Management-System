<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $outbound->No_Shipping }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        .header {
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 11px;
            color: #666;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 120px;
            padding: 4px 0;
            font-weight: bold;
        }
        .info-value {
            display: table-cell;
            padding: 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #333;
            padding: 8px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 20px;
        }
        .signature-box p {
            margin: 5px 0;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 80px;
            padding-top: 5px;
        }
        .notes {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #666;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">WAREHOUSE MANAGEMENT SYSTEM</div>
        <div class="company-info">
            Jl. Contoh No. 123, Jakarta | Telp: (021) 12345678 | Email: info@wms.local
        </div>
    </div>

    <!-- Title -->
    <div class="title">SURAT JALAN</div>

    <!-- Document Info -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">No. Shipping:</div>
            <div class="info-value">{{ $outbound->No_Shipping }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal:</div>
            <div class="info-value">{{ $outbound->Tanggal->format('d F Y') }}</div>
        </div>
    </div>

    <!-- Customer Info -->
    <div style="margin-bottom: 20px;">
        <strong>Kepada Yth:</strong><br>
        <strong style="font-size: 14px;">{{ $outbound->customer->Nama }}</strong><br>
        {{ $outbound->customer->Alamat }}<br>
        Telp: {{ $outbound->customer->Kontak }}
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                <th>Kode SKU</th>
                <th>Nama Barang</th>
                <th class="text-center" style="width: 80px;">Qty</th>
                <th class="text-center" style="width: 80px;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @foreach($outbound->outboundDetails as $index => $detail)
            @php $totalQty += $detail->Qty; @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $detail->SKU }}</td>
                <td>{{ $detail->masterBarang->Nama }}</td>
                <td class="text-right">{{ number_format($detail->Qty) }}</td>
                <td class="text-center">{{ $detail->masterBarang->satuan }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($totalQty) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Notes -->
    @if($outbound->notes)
    <div class="notes">
        <strong>Catatan:</strong><br>
        {{ $outbound->notes }}
    </div>
    @endif

    <!-- Terms -->
    <div style="margin-top: 20px; font-size: 10px;">
        <strong>Syarat & Ketentuan:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Barang yang sudah dikirim tidak dapat ditukar atau dikembalikan</li>
            <li>Mohon periksa barang saat penerimaan</li>
            <li>Komplain diterima maksimal 2x24 jam setelah barang diterima</li>
        </ul>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Pengirim</strong></p>
            <div class="signature-line">
                {{ $outbound->user->name }}
            </div>
        </div>
        <div class="signature-box">
            <p><strong>Penerima</strong></p>
            <div class="signature-line">
                (Nama & Tanda Tangan)
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dokumen ini dicetak secara otomatis oleh sistem pada {{ now()->format('d F Y H:i') }}<br>
        {{ $outbound->No_Shipping }} | Halaman 1 dari 1
    </div>
</body>
</html>
