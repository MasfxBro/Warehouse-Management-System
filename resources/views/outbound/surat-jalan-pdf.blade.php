<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $outbound->No_Surat_Jalan ?? $outbound->No_Shipping }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #1a5f7a;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .company-subtitle {
            font-size: 10px;
            color: #666;
            margin: 0;
        }
        .doc-title {
            text-align: right;
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .divider {
            border-bottom: 2px solid #1a5f7a;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 10px 0 0;
        }
        .box {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            background-color: #fcfcfc;
            min-height: 90px;
        }
        .box-title {
            font-weight: bold;
            font-size: 11px;
            color: #1a5f7a;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .field-label {
            font-weight: bold;
            width: 110px;
            display: inline-block;
            color: #555;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #1a5f7a;
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .signature-box {
            height: 70px;
        }
        .signature-name {
            font-weight: bold;
            border-top: 1px dashed #666;
            padding-top: 5px;
            display: inline-block;
            width: 80%;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="company-title">WAREHOUSE SYSTEM</div>
                <div class="company-subtitle">Outbound &amp; Logistics Management System</div>
                <div class="company-subtitle">Jl. Logistik Utama No. 88, Jakarta</div>
            </td>
            <td style="width: 50%;">
                <div class="doc-title">SURAT JALAN</div>
                <div style="text-align: right; font-size: 11px; color: #555;">
                    No. Surat Jalan: <strong>{{ $outbound->No_Surat_Jalan ?? '-' }}</strong><br>
                    No. Shipping: <strong>{{ $outbound->No_Shipping }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Info Section -->
    <table class="info-table">
        <tr>
            <td>
                <div class="box">
                    <div class="box-title">INFORMASI PENGIRIMAN</div>
                    <div><span class="field-label">Tanggal:</span> {{ $outbound->Tanggal->format('d/m/Y') }}</div>
                    <div><span class="field-label">No. Shipping:</span> {{ $outbound->No_Shipping }}</div>
                    <div><span class="field-label">No. Surat Jalan:</span> {{ $outbound->No_Surat_Jalan ?? '-' }}</div>
                </div>
            </td>
            <td style="padding-right: 0;">
                <div class="box">
                    <div class="box-title">TUJUAN PENGIRIMAN (CUSTOMER)</div>
                    <div><span class="field-label">Nama Customer:</span> <strong>{{ $outbound->customer->Nama }}</strong></div>
                    <div><span class="field-label">Alamat:</span> {{ $outbound->customer->Alamat ?? 'Alamat tidak dicantumkan' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">NO</th>
                <th style="width: 20%;">SKU</th>
                <th style="width: 35%;">NAMA BARANG</th>
                <th style="width: 15%;">RAK / LOKASI</th>
                <th style="width: 15%;">BATCH</th>
                <th style="width: 10%;" class="text-right">QTY</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; $no = 1; @endphp
            @if(isset($pickingList) && count($pickingList) > 0)
                @foreach($pickingList as $item)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td><strong>{{ $item['sku'] }}</strong></td>
                        <td>{{ $item['nama_barang'] }}</td>
                        <td>{{ $item['kode_rak'] }}</td>
                        <td>{{ $item['batch'] }}</td>
                        <td class="text-right"><strong>{{ $item['qty_pick'] }}</strong></td>
                    </tr>
                    @php $totalQty += $item['qty_pick']; @endphp
                @endforeach
            @else
                @foreach($outbound->outboundDetails as $detail)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td><strong>{{ $detail->SKU }}</strong></td>
                        <td>{{ $detail->masterBarang->Nama }}</td>
                        <td>{{ $detail->rackLocation->Kode_Rak }}</td>
                        <td>-</td>
                        <td class="text-right"><strong>{{ $detail->Qty }}</strong></td>
                    </tr>
                    @php $totalQty += $detail->Qty; @endphp
                @endforeach
            @endif
            <tr style="background-color: #f0f4f8; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL QTY KELUAR:</td>
                <td class="text-right" style="color: #1a5f7a; font-size: 12px;">{{ $totalQty }} UNIT</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Table -->
    <table class="signature-table">
        <tr>
            <td>
                <div>Pengirim / Petugas Gudang</div>
                <div class="signature-box"></div>
                <div class="signature-name">( ___________________ )</div>
            </td>
            <td>
                <div>Sopir / Kurir Pengirim</div>
                <div class="signature-box"></div>
                <div class="signature-name">( ___________________ )</div>
            </td>
            <td>
                <div>Penerima / Customer</div>
                <div class="signature-box"></div>
                <div class="signature-name">( {{ $outbound->customer->Nama }} )</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Surat Jalan ini dicetak secara otomatis oleh Warehouse Management System pada {{ date('d/m/Y H:i') }} WIB.
    </div>

</body>
</html>
