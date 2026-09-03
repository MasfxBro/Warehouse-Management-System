<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $outbound->No_Shipping }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1a202c;
            line-height: 1.5;
            padding: 30px 35px;
            padding-bottom: 160px; /* ruang untuk TTD fixed di bawah */
        }
        /* ---- Header ---- */
        .header-wrap {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: middle;
        }
        .header-right { text-align: right; }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0058BE;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 9px;
            color: #718096;
            margin-top: 2px;
        }
        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #1a202c;
            letter-spacing: 1px;
        }
        .doc-no {
            font-size: 10px;
            color: #4a5568;
            margin-top: 3px;
        }
        .divider {
            border: none;
            border-top: 2.5px solid #0058BE;
            margin-bottom: 16px;
        }
        /* ---- Info Boxes ---- */
        .info-wrap {
            display: table;
            width: 100%;
            margin-bottom: 18px;
        }
        .info-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        .info-cell:last-child { padding-right: 0; padding-left: 10px; }
        .box {
            border: 1px solid #cbd5e0;
            border-radius: 5px;
            padding: 10px 12px;
            background: #f8fafc;
            min-height: 80px;
        }
        .box-title {
            font-size: 8px;
            font-weight: bold;
            color: #0058BE;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 7px;
        }
        .info-row { margin-bottom: 4px; }
        .info-label {
            font-weight: bold;
            color: #4a5568;
            display: inline-block;
            width: 115px;
            font-size: 10px;
        }
        .info-value { color: #1a202c; font-size: 10px; }
        /* ---- Items Table ---- */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table th {
            background-color: #0058BE;
            color: #ffffff;
            padding: 7px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .items-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            color: #2d3748;
        }
        .items-table tr:nth-child(even) td { background-color: #f7f9fb; }
        .items-table .total-row td {
            background: #ebf4ff;
            font-weight: bold;
            border-top: 2px solid #0058BE;
            font-size: 11px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'DejaVu Sans Mono', monospace; }
        /* ---- Signature — Fixed di paling bawah halaman A4, di atas footer ---- */
        .sign-wrap {
            position: fixed;
            bottom: 32px;
            left: 35px;
            right: 35px;
            display: table;
            width: calc(100% - 70px);
            page-break-inside: avoid;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }
        .sign-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }
        .sign-cell:first-child { padding-left: 0; }
        .sign-cell:last-child  { padding-right: 0; }
        .sign-role {
            font-size: 10px;
            font-weight: bold;
            color: #4a5568;
            margin-bottom: 3px;
        }
        .sign-note {
            font-size: 9px;
            color: #a0aec0;
            margin-bottom: 50px;
        }
        .sign-line {
            border-top: 1px dashed #718096;
            padding-top: 5px;
            font-size: 10px;
            color: #2d3748;
        }
        /* ---- Footer ---- */
        .footer {
            position: fixed;
            bottom: 8px;
            left: 35px;
            right: 35px;
            text-align: center;
            font-size: 8px;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header-wrap">
        <div class="header-left">
            <div class="company-name">WMS PROTOTIPE 2</div>
            <div class="company-sub">Warehouse Management System — Logistik & Pergudangan</div>
            <div class="company-sub">Jl. Pendidikan Logistik No. 1 | Telp. (021) 000-0000</div>
        </div>
        <div class="header-right">
            <div class="doc-title">SURAT JALAN</div>
            <div class="doc-no">No. SJ: <strong class="mono">{{ $outbound->No_Shipping }}</strong></div>
            <div class="doc-no">Tanggal Cetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
        </div>
    </div>
    <hr class="divider">

    <!-- INFO BOXES -->
    <div class="info-wrap">
        <!-- Informasi Pengiriman -->
        <div class="info-cell">
            <div class="box">
                <div class="box-title">Informasi Pengiriman</div>
                <div class="info-row">
                    <span class="info-label">No. Surat Jalan</span>
                    <span class="info-value mono"><strong>{{ $outbound->No_Shipping }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Kirim</span>
                    <span class="info-value">{{ $outbound->Tanggal->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama Penerima</span>
                    <span class="info-value"><strong>{{ $outbound->Nama_Penerima ?? '-' }}</strong></span>
                </div>
                @if($outbound->Catatan)
                <div class="info-row">
                    <span class="info-label">Catatan</span>
                    <span class="info-value">{{ $outbound->Catatan }}</span>
                </div>
                @endif
            </div>
        </div>
        <!-- Detail Customer -->
        <div class="info-cell">
            <div class="box">
                <div class="box-title">Tujuan Pengiriman (Customer)</div>
                <div class="info-row">
                    <span class="info-label">Nama Customer</span>
                    <span class="info-value"><strong>{{ $outbound->customer->Nama ?? '-' }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Alamat</span>
                    <span class="info-value">{{ $outbound->customer->Alamat ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">No. Kontak</span>
                    <span class="info-value mono">{{ $outbound->customer->No_Kontak ?? ($outbound->customer->Kontak ?? '-') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL BARANG -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width:5%">No</th>
                <th style="width:18%" class="mono">SKU</th>
                <th style="width:40%">Nama Barang</th>
                <th style="width:17%">Satuan</th>
                <th class="text-right" style="width:10%">Qty</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @foreach($outbound->outboundDetails as $i => $detail)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="mono"><strong>{{ $detail->SKU }}</strong></td>
                    <td>{{ $detail->masterBarang->Nama ?? '-' }}</td>
                    <td>{{ $detail->masterBarang->Kategori ?? '-' }}</td>
                    <td class="text-right mono"><strong>{{ number_format($detail->Qty) }}</strong></td>
                </tr>
                @php $totalQty += $detail->Qty; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL QTY KELUAR:</td>
                <td class="text-right mono">{{ number_format($totalQty) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- AREA TANDA TANGAN -->
    <div class="sign-wrap">
        <div class="sign-cell">
            <div class="sign-role">Pengirim</div>
            <div class="sign-note">Siswa / Petugas Logistik</div>
            <div class="sign-line">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
        </div>
        <div class="sign-cell">
            <div class="sign-role">Penerima / Kurir</div>
            <div class="sign-note">{{ $outbound->Nama_Penerima ?? '___________' }}</div>
            <div class="sign-line">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
        </div>
        <div class="sign-cell">
            <div class="sign-role">Supervisor</div>
            <div class="sign-note">Guru / Admin Gudang</div>
            <div class="sign-line">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Dokumen ini dicetak otomatis oleh Warehouse Management System Prototipe 2.
        Sah tanpa tanda tangan basah jika dikeluarkan oleh sistem.
    </div>

</body>
</html>
