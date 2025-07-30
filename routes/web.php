<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

// 1. Menampilkan form
Route::get('/', [InvoiceController::class, 'create'])->name('invoice.create');

// 2. Menyimpan data dari form
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

// 3. Menangani output (bisa preview atau download)
Route::get('/invoices/{invoice}/output/{action}', [InvoiceController::class, 'handleOutput'])->name('invoice.output');

// 4. Menampilkan halaman draft
Route::get('/invoices/drafts', [InvoiceController::class, 'showDrafts'])->name('invoices.drafts');

// 5. RUTE BARU: Menghapus draft berdasarkan ID
Route::get('/invoices/drafts/{id}/delete', [InvoiceController::class, 'deleteDraft'])->name('invoices.drafts.delete');

// 6. RUTE BARU: Khusus untuk download ulang dari halaman draft
Route::get('/invoices/drafts/{invoice}/redownload', [InvoiceController::class, 'redownloadFromDraft'])->name('invoices.drafts.redownload');
