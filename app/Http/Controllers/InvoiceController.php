<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    // Menampilkan form
    public function create()
    {
        return view('invoice.create');
    }

    // Menyimpan data & membuat PDF
    public function store(Request $request)
    {
        // Validasi data yang masuk dari form
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'address_name' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Gunakan transaction untuk memastikan semua data berhasil disimpan
        DB::beginTransaction();
        try {
            // Cari atau buat customer baru
            $customer = Customer::updateOrCreate(
            ['name' => $request->customer_name],
            ['address' => $request->address_name]
        );
            // Hitung total dari semua item
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            // Simpan data utama ke tabel 'invoices'
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_number' => 'INV-' . time(), // Nanti bisa dibuat lebih baik
                'invoice_date' => $request->invoice_date,
                'total_amount' => $totalAmount,
            ]);

            // Simpan setiap item ke tabel 'invoice_items'
            foreach ($request->items as $itemData) {
    $invoice->items()->create([
        'name' => $itemData['name'],
        'quantity' => $itemData['quantity'],
        'price' => $itemData['price'],
        // Tambahkan baris ini untuk menyimpan subtotal per item
        'subtotal' => $itemData['quantity'] * $itemData['price'], 
    ]);
}

            DB::commit(); // Jika semua berhasil, simpan permanen

            // Arahkan ke halaman download PDF
            return redirect()->route('invoice.output', ['invoice' => $invoice, 'action' => $request->action]);

        } catch (\Exception $e) {
            DB::rollBack(); // Jika ada error, batalkan semua penyimpanan
            return back()->withErrors(['error' => 'Gagal menyimpan invoice: ' . $e->getMessage()]);
        }
    }

    // Membuat dan men-download PDF
    public function downloadPDF(Invoice $invoice, $action)
    {
        // Load relasi agar bisa diakses di view PDF
        $invoice->load('customer', 'items'); 

        // Membuat nama file: Contoh Patra_Raya-Juli_2025.pdf
        $customerName = str_replace(' ', '_', $invoice->customer->name);
        $monthName = Carbon::parse($invoice->invoice_date)->locale('id')->isoFormat('MMMM');
        $year = Carbon::parse($invoice->invoice_date)->format('Y');
        $fileName = "{$customerName}-{$monthName}_{$year}.pdf";

        // Ganti ini dengan nama file template PDF Anda
        $pdf = Pdf::loadView('invoice.pdf_template', ['invoice' => $invoice]);
        
        if ($action === 'preview') {
            // Jika aksi adalah 'preview', tampilkan PDF di browser
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
        // return $pdf->stream($fileName);
    }
}