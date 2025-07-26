<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #000;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            text-align: right;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border: none;
            border-collapse: collapse;
        }
        .info-table td {
            border: none;
            padding: 2px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .items-table th, .items-table td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
        }
        .items-table thead th {
            background-color: #E9E9E9;
            text-align: center;
        }
        .totals-table {
            width: 60%;
            float: right;
            border: none;
            border-collapse: collapse;
        }
        .totals-table td {
            border: none;
            padding: 5px 8px;
        }
        hr {
            border-top: 1px solid #555;
            border-bottom: none;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        {{-- BAGIAN HEADER --}}
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    Tanggal: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <div class="invoice-title">INVOICE</div>
                </td>
            </tr>
        </table>

        <hr>

        {{-- BAGIAN INFO PENGIRIM & PELANGGAN --}}
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                <strong strong>PERUSAHAAN:</strong><br>
                    <strong>Bagas Khairudin</strong><br>
                    Jl Kurma, Blok O Nomor 235<br>
                    82232601312
                </td>
                <td style="width: 50%;">
                    <strong>PELANGGAN:</strong><br>
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    {{ $invoice->customer->address}}
                </td>
            </tr>
        </table>
        
        {{-- BAGIAN TABEL ITEM --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan<br>
                      Per Rit</th>
                    <th>SubTotal</th>
                    <th>PPN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                  <tr>
                      <td>{{ $item->name }}</td>
                      <td style="text-align: center;">{{ $item->quantity }}</td>
                      <td style="text-align: right;">{{ number_format($item->price, 0, ',', '.') }}</td>
                      {{-- Menghitung Jumlah * Harga --}}
                      <td style="text-align: right;">{{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
                      {{-- Menampilkan tanda strip (-) --}}
                      <td style="text-align: center;">-</td>
                  </tr>
                  @endforeach
            </tbody>
        </table>

        {{-- BAGIAN INFO PEMBAYARAN & TOTAL --}}
        <table class="info-table">
            <tr>
                {{-- Info Bank --}}
                <td style="width: 50%; vertical-align: top;">
                    <strong>Pembayaran Transfer:</strong><br>
                    Bank: BCA<br>
                    No Rek: 82038472145<br>
                    Atas Nama: Bagas Khairudin
                </td>
                {{-- Info Total --}}
                <td style="width: 50%; vertical-align: top;">
                    <table class="totals-table">
                        <tr>
                            <td>SUB TOTAL</td>
                            <td style="text-align: right;">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>PPN</td>
                            <td style="text-align: right;">-</td> {{-- Ganti jika ada data PPN --}}
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Total</td>
                            <td style="font-weight: bold; text-align: right;">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>