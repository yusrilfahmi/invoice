<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Invoice Baru</title>
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 2rem; }
        .container { max-width: 900px; margin: auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #34495e; }
        input[type="text"], input[type="date"], input[type="number"], select { width: 100%; padding: 0.75rem; border: 1px solid #bdc3c7; border-radius: 4px; box-sizing: border-box; }
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

        <form action="{{ route('invoices.store') }}" method="POST" target="_blank">
            @csrf
            <div x-data="invoiceForm()" @input="calculateTotal()">

              {{-- OPSI BARU UNTUK JENIS LAPORAN --}}
              <div style="margin-bottom: 1rem;">
                    <label for="report_type">Jenis Laporan:</label>
                    <select name="type" x-model="reportType" @change="onReportTypeChange()" style="width:100%; padding: 0.75rem; border: 1px solid #bdc3c7; border-radius: 4px;">
                        <option value="invoice">Invoice</option>
                        <option value="retribusi">Invoice Retribusi</option>
                    </select>
                </div>

                {{-- Info Pelanggan & Tanggal --}}
<div style="display: flex; gap: 2rem; margin-bottom: 1rem; align-items: flex-start;">

    {{-- KELOMPOK 1: PILIHAN PELANGGAN (Dropdown ATAU Input Baru) --}}
    <div style="flex: 2;"> {{-- Dibuat lebih lebar --}}
        {{-- Dropdown Pelanggan Lama --}}
        <div x-show="!isNewCustomer">
            <label for="customer_id">Pilih Pelanggan:</label>
            <select id="customer_id" name="customer_id" x-model="selectedCustomerId" @change="updateCustomerData()">
                <option value="">-- Pilih Pelanggan --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" data-address="{{ $customer->address }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            <input type="text" name="customer_address" x-model="selectedCustomerAddress" placeholder="Alamat akan terisi otomatis" style="margin-top: 0.5rem;">
        </div>

        {{-- Input Pelanggan Baru --}}
        <div x-show="isNewCustomer" style="display: flex; gap: 1rem;">
            <div style="flex: 1;">
                <label for="new_customer_name">Nama Pelanggan Baru:</label>
                <input type="text" name="new_customer_name" placeholder="Masukkan nama pelanggan baru">
            </div>
            <div style="flex: 1;">
                <label for="new_customer_address">Alamat Pelanggan Baru:</label>
                <input type="text" name="new_customer_address" placeholder="Masukkan alamat pelanggan baru">
            </div>
        </div>

        {{-- Checkbox dipindahkan ke bawah sini --}}
        <div style="margin-top: 10px;">
            <input type="checkbox" id="new_customer_toggle" x-model="isNewCustomer" style="width: auto;">
            <label for="new_customer_toggle" style=" display: inline;">Pelanggan Baru?</label>
        </div>
    </div>

    {{-- KELOMPOK 2: TANGGAL INVOICE --}}
    <div style="flex: 1;">
        <label for="invoice_date">Tanggal Invoice:</label>
        <input type="date" id="invoice_date" name="invoice_date" value="{{ date('Y-m-d') }}" required>
    </div>

</div>

                {{-- Tabel Item --}}
                <table>
                    <thead>
                        <tr>
                            <th x-text="reportType === 'retribusi' ? 'Tanggal' : 'Nama Item'"></th>
                            <th x-text="reportType === 'retribusi' ? 'Berat (Kg)' : 'Jumlah'"></th>

                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td>
                                    <input :type="reportType === 'retribusi' ? 'date' : 'date'" x-model="item.name" :name="`items[${index}][name]`" required>
                                </td>
                                <td>
                                    <input type="number" x-model.number="item.quantity" :name="`items[${index}][quantity]`" @input="calculateSubtotal(index)" min="1" required>
                                </td>

                                <td>
                                    {{-- Tampilkan hasil kalkulasi subtotal --}}
                                    <span x-text="formatRupiah(item.subtotal)"></span>
                                </td>
                                <td>
                                    <button type="button" @click="removeItem(index)" class="remove-btn">Hapus</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <button type="button" @click="addItem()">+ Tambah Baris</button>

                <div class="totals">
                    Total: <span x-text="formatRupiah(total)"></span>
                </div>

                <hr>

                {{-- Input tersembunyi untuk memberitahu controller aksi apa yang dipilih --}}
                <input type="hidden" name="action" id="form-action">

                {{-- DUA TOMBOL BARU --}}
                <button type="submit" onclick="document.getElementById('form-action').value = 'preview'">
                    Preview
                </button>

                <button type="submit" onclick="document.getElementById('form-action').value = 'download'">
                    Download PDF
                </button>
            </div>
        </form>
    </div>

    <script>
        function invoiceForm() {
            const today = new Date().toISOString().slice(0, 10);

            return {
                // DATA UTAMA
                reportType: 'invoice',
                today: today,
                
                // DATA UNTUK BARIS ITEM
                items: [{ name: '', quantity: 1, price: 350000, subtotal: 0 }],
                total: 0,

                // DATA UNTUK DROPDOWN PELANGGAN
                isNewCustomer: false,
                selectedCustomerId: '',
                selectedCustomerAddress: '',
                customers: @json($customers),

                // FUNGSI UNTUK MENANGANI PERUBAHAN JENIS LAPORAN
                onReportTypeChange() {
                    // Reset items dan sesuaikan dengan jenis laporan
                    if (this.reportType === 'retribusi') {
                        this.items = [{ name: this.today, quantity: 1, price: 30, subtotal: 0 }];
                    } else {
                        this.items = [{ name: '', quantity: 1, price: 350000, subtotal: 0 }];
                    }
                    this.calculateTotal();
                },

                // FUNGSI UNTUK DROPDOWN PELANGGAN
                updateCustomerData() {
                    if (!this.selectedCustomerId) {
                        this.selectedCustomerAddress = '';
                        return;
                    }
                    const selectedCustomer = this.customers.find(c => c.id == this.selectedCustomerId);
                    this.selectedCustomerAddress = selectedCustomer ? selectedCustomer.address : '';
                },

                // FUNGSI UNTUK BARIS ITEM
                addItem() {
                    let newItem;
                    if (this.reportType === 'retribusi') {
                        newItem = { name: this.today, quantity: 1, price: 30, subtotal: 30 };
                    } else {
                        newItem = { name: '', quantity: 1, price: 350000, subtotal: 350000 };
                    }
                    this.items.push(newItem);
                    this.calculateTotal();
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                    this.calculateTotal();
                },

                calculateSubtotal(index) {
                    const item = this.items[index];
                    if (this.reportType === 'retribusi') {
                        // Kalkulasi untuk retribusi: Berat (quantity) * 30
                        item.subtotal = item.quantity * 30;
                    } else {
                        // Kalkulasi untuk invoice biasa: Jumlah * 350000 (harga tetap)
                        item.subtotal = item.quantity * 350000;
                    }
                    this.calculateTotal();
                },

                calculateTotal() {
                    // Hitung ulang semua subtotal terlebih dahulu
                    this.items.forEach((item, index) => {
                        if (this.reportType === 'retribusi') {
                            item.subtotal = item.quantity * 30;
                        } else {
                            item.subtotal = item.quantity * 350000;
                        }
                    });
                    // Kemudian hitung total
                    this.total = this.items.reduce((acc, item) => acc + item.subtotal, 0);
                },

                formatRupiah(number) {
                    return new Intl.NumberFormat('id-ID', { 
                        style: 'currency', 
                        currency: 'IDR', 
                        minimumFractionDigits: 0 
                    }).format(number);
                },

                // INISIALISASI
                init() {
                    this.calculateTotal();
                }
            }
        }
    </script>

    {{-- Error Messages --}}
    <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: none;" id="error-messages">
        <strong>Oops! Ada yang salah dengan data Anda:</strong>
        <ul style="margin-top: 0.5rem; margin-bottom: 0;" id="error-list">
        </ul>
    </div>
</body>
</html>