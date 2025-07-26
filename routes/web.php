<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

// Route untuk MENAMPILKAN form (metode GET)
Route::get('/', [InvoiceController::class, 'create'])->name('invoice.create');

// Route untuk MENYIMPAN data dari form (metode POST)
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

// Route untuk men-download PDF setelah disimpan
Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'downloadPDF'])->name('invoice.download');