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
        {{-- Mendorong tanggal ke bawah, rata kiri --}}
        <td style="width: 50%; vertical-align: bottom;">
            Tanggal: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
        </td>

        {{-- Mendorong judul ke bawah, rata kanan --}}
        <td style="width: 50%; text-align: right; vertical-align: bottom;">
            <div class="invoice-title">
                @if($invoice->type === 'retribusi')
                    INVOICE <br>
                    RETRIBUSI
                @else
                    INVOICE
                @endif
            </div>
        </td>
    </tr>
</table>

        <hr>

        {{-- BAGIAN INFO PENGIRIM & PELANGGAN --}}
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                <strong strong>PERUSAHAAN:</strong><br>
                    <strong>@if($invoice->type === 'retribusi') Sukayat @else Bagas Khairudin @endif</strong><br>
                    {{-- <strong>Sukayat</strong><br> --}}
                    Cerme Indah, Jl Kurma, RT 6 RW 4 Blok O Nomor 235<br>
                    @if($invoice->type === 'retribusi') 081330397993 @else 082232601312 @endif
                    
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
            <th>@if($invoice->type === 'retribusi') Tanggal @else Nama @endif</th>
            <th>@if($invoice->type === 'retribusi') Satuan Berat (Kg) @else Jumlah @endif</th>

            {{-- Sembunyikan Harga Satuan jika retribusi --}}
            @if($invoice->type !== 'retribusi')
                <th>Harga Satuan Per Rit</th>
            @endif

            <th>SubTotal</th>

            {{-- TAMBAHKAN KEMBALI BLOK INI --}}
            @if($invoice->type !== 'retribusi')
                <th>PPN</th>
            @endif
            {{-- AKHIR BLOK TAMBAHAN --}}
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td style="text-align: center;">{{ $item->quantity }}</td>

            {{-- Sembunyikan Harga Satuan jika retribusi --}}
            @if($invoice->type !== 'retribusi')
                <td style="text-align: right;">{{ number_format(350000, 0, ',', '.') }}</td>
            @endif

            <td style="text-align: right;">
                @if($invoice->type === 'retribusi')
                    {{ number_format($item->quantity * 30, 0, ',', '.') }}
                @else
                    {{ number_format($item->quantity * 350000, 0, ',', '.') }}
                @endif
            </td>
            {{-- TAMBAHKAN KEMBALI BLOK INI --}}
            @if($invoice->type !== 'retribusi')
                <td style="text-align: center;">-</td>
            @endif
            {{-- AKHIR BLOK TAMBAHAN --}}
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
                    @if($invoice->type === 'retribusi') Atas Nama: Sukayat @else Atas Nama: Bagas Khairudin @endif<br>
                    @if($invoice->type === 'retribusi') No Rek: <> @else No Rek: 1501157479 @endif<br>
                    @if($invoice->type === 'retribusi') Bank: Bank Jatim @else Bank: BCA @endif
                    
                </td>
                {{-- Info Total --}}
                <td style="width: 50%; vertical-align: top;">
                  <table class="totals-table">
                      <tr>
                          <td>SUB TOTAL</td>
                          <td style="text-align: right;">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                      </tr>

                      {{-- Sembunyikan PPN jika retribusi --}}
                      @if($invoice->type !== 'retribusi')
                      <tr>
                          <td>PPN</td>
                          <td style="text-align: right;">-</td>
                      </tr>
                      @endif

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