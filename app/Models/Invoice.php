<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'invoice_date',
        'total_amount',
    ];

    // Relasi: Satu Invoice dimiliki oleh satu Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi: Satu Invoice memiliki banyak Item
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}