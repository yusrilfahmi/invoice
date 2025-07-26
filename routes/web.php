<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

// 1. Menampilkan form
Route::get('/', [InvoiceController::class, 'create'])->name('invoice.create');

// 2. Menyimpan data dari form
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

// 3. Menangani output (bisa preview atau download)
Route::get('/invoices/{invoice}/output/{action}', [InvoiceController::class, 'handleOutput'])->name('invoice.output');