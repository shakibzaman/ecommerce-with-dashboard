<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'payable_id',
        'payable_type',
        'amount',
        'transaction_type',
        'created_by',
        'note',
    ];

    /**
     * Get the parent payable model (supplier, wholesaler, customer).
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the transaction.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
