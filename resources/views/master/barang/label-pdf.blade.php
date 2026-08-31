<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Label Barang — {{ $item->SKU }}</title>
    <style>
        /*
        ================================================================
        LABEL PDF — WMS Prototipe 2
        Variabel tersedia untuk kamu edit desainnya:
          - $item->SKU         : Kode SKU barang
          - $item->Nama        : Nama barang
          - $item->Kategori    : Kategori barang
          - $item->Min_Stok    : Minimum stok
          - $item->stok        : Stok saat ini (real-time)
          - $rackName          : Lokasi rak (string)
          - $qrString          : Payload text QR code
          - $qrBase64          : QR code image (base64 PNG)
          - $printedAt         : Waktu cetak (d/m/Y H:i)

        CATATAN DomPDF:
          - Gunakan inline CSS atau <style> tag, bukan Tailwind/external CSS
          - Float dan table layout lebih reliable daripada flexbox/grid
          - Font yang tersedia: DejaVu Sans (support Unicode/ID)
          - Gambar gunakan format: data:image/png;base64,...
        ================================================================
        */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Wrapper halaman ── */
        .page {
            width: 100%;
            padding: 30px;
        }

        /* ── Card label utama ── */
        .label-card {
            border: 2px solid #0058be;
            border-radius: 8px;
            overflow: hidden;
            max-width: 500px;
            margin: 0 auto;
        }

        /* ── Header biru ── */
        .label-header {
            background-color: #0058be;
            color: #ffffff;
            padding: 12px 16px;
            text-align: center;
        }
        .label-header .app-name {
            font-size: 9px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            opacity: 0.8;
        }
        .label-header .label-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 2px;
        }
        .label-header .sku-code {
            font-size: 13px;
            font-family: 'Courier New', monospace;
            background: rgba(255,255,255,0.15);
            padding: 2px 8px;
            border-radius: 4px;
            margin-top: 4px;
            display: inline-block;
        }

        /* ── Body: QR kiri, info kanan ── */
        .label-body {
            padding: 16px;
        }
        .label-body table {
            width: 100%;
            border-collapse: collapse;
        }
        .col-qr {
            width: 150px;
            vertical-align: middle;
            text-align: center;
            padding-right: 16px;
        }
        .col-qr img {
            width: 140px;
            height: 140px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px;
        }
        .col-qr .qr-caption {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 4px;
        }
        .col-info {
            vertical-align: top;
        }

        /* ── Info rows ── */
        .info-nama {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }
        .info-row {
            margin-bottom: 6px;
        }
        .info-key {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 1px;
        }
        .info-val {
            font-size: 11px;
            color: #1e293b;
            font-weight: bold;
        }
        .info-val.mono {
            font-family: 'Courier New', monospace;
            color: #0058be;
        }

        /* ── Badge stok ── */
        .stok-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .stok-safe {
            background-color: #d1fae5;
            color: #065f46;
        }
        .stok-low {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* ── Footer ── */
        .label-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 8px 16px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
        .label-footer strong {
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="label-card">

        {{-- ===== HEADER ===== --}}
        <div class="label-header">
            <div class="app-name">WMS Prototipe 2 &mdash; SMK Logistik</div>
            <div class="label-title">Label Barang Gudang</div>
            <div class="sku-code">{{ $item->SKU }}</div>
        </div>

        {{-- ===== BODY: QR + INFO ===== --}}
        <div class="label-body">
            <table>
                <tr>
                    {{-- QR Code --}}
                    <td class="col-qr">
                        <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Code {{ $item->SKU }}">
                        <div class="qr-caption">Scan untuk info barang</div>
                    </td>

                    {{-- Info Barang --}}
                    <td class="col-info">
                        <div class="info-nama">{{ $item->Nama }}</div>

                        <div class="info-row">
                            <div class="info-key">SKU</div>
                            <div class="info-val mono">{{ $item->SKU }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-key">Kategori</div>
                            <div class="info-val">{{ $item->Kategori ?? '-' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-key">Lokasi Rak</div>
                            <div class="info-val">{{ $rackName }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-key">Min. Stok</div>
                            <div class="info-val">{{ number_format($item->Min_Stok) }} unit</div>
                        </div>

                    </td>
                </tr>
            </table>
        </div>

        {{-- ===== FOOTER ===== --}}
        <div class="label-footer">
            <strong>WMS Prototipe 2</strong> &bull; Dicetak: {{ $printedAt }} WIB &bull; {{ $qrString }}
        </div>

    </div>
</div>
</body>
</html>
