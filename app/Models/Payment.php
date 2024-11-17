<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['payable_id', 'payable_type', 'amount', 'payment_type', 'payment_date', 'payment_method', 'note', 'invoice_id', 'transaction_id', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
