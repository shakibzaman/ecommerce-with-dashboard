<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'store_id',
        'invoice_type',
        'user_id',
        'date',
        'total',
        'discount',
        'payable_amount',
        'paid',
        'due',
        'created_by',
        'image'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function products()
    {
        return $this->hasMany(InvoiceProduct::class, 'invoice_id');
    }
    public function causer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
