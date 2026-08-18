<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Pengiriman</title>
</head>

<body>

    <h1>Tambah Pengiriman Barang</h1>

    @if ($errors->any())
        <div style="background-color: #ffebee; border: 1px solid #f44336; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <strong style="color: #c62828;">Error!</strong>
            <ul style="margin: 10px 0 0 0; color: #c62828;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('outbound.store') }}" method="POST">
    @csrf

        <h2>Informasi Pengiriman</h2>

        <div>
            <label>No. Shipping</label>
            <br>
            <input type="text" name="No_Shipping" placeholder="Contoh: SHP-2026-0001" value="{{ old('No_Shipping') }}">
        </div>

        <br>

        <div>
            <label>Tanggal Pengiriman</label>
            <br>
            <input type="date" name="Tanggal" value="{{ old('Tanggal') }}">
        </div>

        <br>

        <div>
            <label>Customer</label>
            <br>
            <select name="Customer_ID">
    <option value="">-- Pilih Customer --</option>

    @foreach ($customers as $customer)
        <option value="{{ $customer->Customer_ID }}" {{ old('Customer_ID') == $customer->Customer_ID ? 'selected' : '' }}>
            {{ $customer->Nama }}
        </option>
    @endforeach
</select>
        </div>

        <br>

        <div>
            <label>No. Surat Jalan</label>
            <br>
            <input type="text" name="No_Surat_Jalan" placeholder="Contoh: SJ-2026-0001" value="{{ old('No_Surat_Jalan') }}">
        </div>

        <hr>

        <h2>Detail Barang</h2>

        <div>
            <label>SKU</label>
            <br>
            <select name="SKU">
    <option value="">-- Pilih Barang --</option>

    @foreach ($barang as $item)
        <option value="{{ $item->SKU }}" {{ old('SKU') == $item->SKU ? 'selected' : '' }}>
            {{ $item->SKU }} - {{ $item->Nama }}
        </option>
    @endforeach
</select>
        </div>

        <br>

        <div>
            <label>Rak Pengambilan</label>
            <br>
            <select name="Rack_ID">
    <option value="">-- Pilih Rak --</option>

    @foreach ($racks as $rack)
        <option value="{{ $rack->Rack_ID }}" {{ old('Rack_ID') == $rack->Rack_ID ? 'selected' : '' }}>
            {{ $rack->Kode_Rak }}
        </option>
    @endforeach
</select>
        </div>

        <br>

        <div>
            <label>Jumlah Barang</label>
            <br>
            <input type="number" name="Qty" min="1" placeholder="Masukkan jumlah" value="{{ old('Qty') }}">
        </div>

        <br>

        <button type="submit">
            Simpan Pengiriman
        </button>

    </form>

    <br>

    <a href="{{ route('outbound.index') }}">
        ← Kembali
    </a>

</body>
</html>