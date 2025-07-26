<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Invoice Baru</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 2rem; }
        .container { max-width: 900px; margin: auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #34495e; }
        input[type="text"], input[type="date"], input[type="number"] { width: 100%; padding: 0.75rem; border: 1px solid #bdc3c7; border-radius: 4px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #dfe6e9; padding: 0.75rem; text-align: left; }
        thead { background-color: #3498db; color: white; }
        .totals { margin-top: 1rem; text-align: right; font-size: 1.2rem; font-weight: bold; }
        button { background-color: #3498db; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer; font-size: 1rem; margin-top: 1rem; }
        button:hover { background-color: #2980b9; }
        .remove-btn { background-color: #e74c3c; }
        .remove-btn:hover { background-color: #c0392b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Buat Invoice Baru</h1>
        <hr>

        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf
            <div x-data="invoiceForm()">
                {{-- Info Pelanggan & Tanggal --}}
                <div style="display: flex; gap: 2rem; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <label for="customer_name">Nama Pelanggan:</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="invoice_date">Tanggal Invoice:</label>
                        <input type="date" id="invoice_date" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                {{-- Tabel Item --}}
                <table>
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td><input type="text" x-model="item.name" :name="`items[${index}][name]`" required></td>
                                <td><input type="number" x-model.number="item.quantity" :name="`items[${index}][quantity]`" @input="calculateSubtotal(index)" min="1" required></td>
                                <td><input type="number" x-model.number="item.price" :name="`items[${index}][price]`" @input="calculateSubtotal(index)" min="0" required></td>
                                <td><span x-text="formatRupiah(item.subtotal)"></span></td>
                                <td><button type="button" @click="removeItem(index)" class="remove-btn">Hapus</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <button type="button" @click="addItem()">+ Tambah Baris</button>

                <div class="totals">
                    Total: <span x-text="formatRupiah(total)"></span>
                </div>

                <hr>
                <button type="submit">Simpan & Buat PDF</button>
            </div>
        </form>
    </div>

    <script>
        function invoiceForm() {
            return {
                items: [{ name: '', quantity: 1, price: 0, subtotal: 0 }],
                total: 0,
                addItem() { this.items.push({ name: '', quantity: 1, price: 0, subtotal: 0 }); },
                removeItem(index) { this.items.splice(index, 1); this.calculateTotal(); },
                calculateSubtotal(index) {
                    const item = this.items[index];
                    item.subtotal = item.quantity * item.price;
                    this.calculateTotal();
                },
                calculateTotal() { this.total = this.items.reduce((acc, item) => acc + item.subtotal, 0); },
                formatRupiah(number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); }
            }
        }
    </script>
    {{-- Letakkan kode ini di bawah <h1> --}}

@if ($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <strong>Oops! Ada yang salah dengan data Anda:</strong>
        <ul style="margin-top: 0.5rem; margin-bottom: 0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
</body>
</html>