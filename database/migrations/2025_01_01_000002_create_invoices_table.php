<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('invoice_items', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id'); // Definisikan kolomnya dulu
        $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade'); // Baru definisikan sambungannya
        $table->string('name');
        $table->integer('quantity');
        $table->decimal('price', 15, 2);
        $table->decimal('subtotal', 15, 2); // <-- TAMBAHKAN BARIS INI
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
