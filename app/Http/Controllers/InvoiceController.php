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
        // return view('invoice.create');
        $customers = Customer::orderBy('name')->get();

    // Kirim data $customers ke view
    return view('invoice.create', ['customers' => $customers]);
    }

    // Menyimpan data & membuat PDF
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:invoice,retribusi',
            'customer_id' => 'required_without:new_customer_name|nullable|exists:customers,id',
            'customer_address' => 'nullable|string|max:255',
            'new_customer_name' => 'required_without:customer_id|nullable|string|max:255',
            'new_customer_address' => 'required_with:new_customer_name|nullable|string|max:255',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $customer = null;

            if ($request->filled('new_customer_name')) {
                $customer = Customer::create([
                    'name' => $request->new_customer_name,
                    'address' => $request->new_customer_address,
                ]);
            } else {
                $customer = Customer::findOrFail($request->customer_id);
                if ($request->filled('customer_address')) {
                    $customer->address = $request->customer_address;
                    $customer->save();
                }
            }

            $totalAmount = 0;
            if ($request->type === 'retribusi') {
        // Logika baru: Jumlahkan semua berat lalu kali 30
        $totalWeight = 0;
        foreach ($request->items as $item) {
            $totalWeight += $item['quantity']; // 'quantity' di sini adalah berat
        }
        $totalAmount = $totalWeight * 30;
    } else {
    // Logika lama untuk invoice biasa
    foreach ($request->items as $item) {
        $totalAmount += $item['quantity'] * 350_000;
    }
}

        $invoice = Invoice::create([
            'type' => $request->type,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-' . time(),
            'invoice_date' => $request->invoice_date,
            'total_amount' => $totalAmount,
        ]);

        foreach ($request->items as $itemData) {
    // Definisikan $itemName dengan nilai default
    $itemName = $itemData['name']; 
    
    // Jika jenisnya retribusi, ubah nilainya
    if ($request->type === 'invoice') {
        $itemName = 'pembuangan sampah pemukiman tanggal ' . Carbon::parse($itemData['name'])->format('d/m/Y');
    } else if ($request->type === 'retribusi') {
        $itemName = Carbon::parse($itemData['name'])->format('d/m/Y');
    }
    
    // Tentukan harga berdasarkan type
    if ($request->type === 'retribusi') {
        $price = 30; // Harga tetap untuk retribusi
    } else {
        $price = 350_000; // Harga tetap untuk invoice
    }
    
    // Simpan item dengan harga yang sudah ditentukan
    $invoice->items()->create([
        'name' => $itemName,
        'quantity' => $itemData['quantity'],
        'price' => $price,
        'subtotal' => $itemData['quantity'] * $price,
    ]);
}
        
        DB::commit();
        return redirect()->route('invoice.output', ['invoice' => $invoice, 'action' => $request->action]);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Gagal menyimpan invoice: ' . $e->getMessage()]);
    }
}

    // Membuat dan men-download PDF
    public function handleOutput(Invoice $invoice, $action)
    {
        // Load relasi agar bisa diakses di view PDF
        $invoice->load('customer', 'items'); 

        // Membuat nama file: Contoh Patra_Raya-Juli_2025.pdf
        $invoiceType = $invoice->type;
        $customerName = str_replace(' ', '_', $invoice->customer->name);
        $monthName = Carbon::parse($invoice->invoice_date)->locale('id')->isoFormat('MMMM');
        $year = Carbon::parse($invoice->invoice_date)->format('Y');
        $fileName = "{$invoiceType}-{$customerName}-{$monthName}_{$year}.pdf";

        // Ganti ini dengan nama file template PDF Anda
        $pdf = Pdf::loadView('invoice.pdf_template', ['invoice' => $invoice]);
        
        if ($action === 'preview') {
            // Jika aksi adalah 'preview', tampilkan PDF di browser
            return $pdf->stream($fileName);
        }
        if ($action === 'download') {
            // Logika untuk menyimpan ke database diletakkan di sini
            DB::table('draft_pdfs')->insert([
                'nama_file' => $fileName,
                'invoice_id' => $invoice->id,
                'tanggal_download' => $invoice->invoice_date,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Setelah disimpan, baru kirim file untuk diunduh
            return $pdf->download($fileName);
        }

        // Fallback jika action tidak dikenali (misal: URL diakses manual)
        return redirect()->back()->withErrors(['error' => 'Aksi tidak valid.']);
    }

    public function showDrafts()
    {
        $drafts = DB::table('draft_pdfs')
                    ->orderBy('tanggal_download', 'desc') // Urutkan dari yang terbaru
                    ->get()
                    ->groupBy(function($item) {
                        // Mengelompokkan data berdasarkan tanggal, bulan, dan tahun.
                        // Contoh format: 31 Juli 2025
                        return Carbon::parse($item->tanggal_download)->locale('id')->isoFormat('D MMMM YYYY');
                    });

        // Kirim data yang sudah dikelompokkan ke view 'invoice.drafts'
        return view('invoice.drafts', ['draftsByDate' => $drafts]);
    }

    public function deleteDraft($id)
    {
        // Cari draft berdasarkan ID
        $draft = DB::table('draft_pdfs')->where('id', $id);

        // Jika draft ditemukan, hapus
        if ($draft->exists()) {
            $draft->delete();
            // Redirect kembali ke halaman draft dengan pesan sukses
            return redirect()->route('invoices.drafts')->with('success', 'Draft berhasil dihapus.');
        }

        // Jika draft tidak ditemukan, redirect dengan pesan error
        return redirect()->route('invoices.drafts')->withErrors(['error' => 'Draft tidak ditemukan.']);
    }

    public function redownloadFromDraft(Invoice $invoice)
    {
        // Load relasi yang diperlukan
        $invoice->load('customer', 'items');

        // Buat nama file persis seperti sebelumnya
        $invoiceType = $invoice->type;
        $customerName = str_replace(' ', '_', $invoice->customer->name);
        $monthName = Carbon::parse($invoice->invoice_date)->locale('id')->isoFormat('MMMM');
        $year = Carbon::parse($invoice->invoice_date)->format('Y');
        $fileName = "{$invoiceType}-{$customerName}-{$monthName}_{$year}.pdf";

        // Buat objek PDF
        $pdf = Pdf::loadView('invoice.pdf_template', ['invoice' => $invoice]);

        // Langsung kirim file untuk diunduh, tanpa menyimpan ke database
        return $pdf->download($fileName);
    }
}