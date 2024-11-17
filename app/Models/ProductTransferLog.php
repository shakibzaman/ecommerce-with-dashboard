<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransferLog extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'transfer_from', 'transfer_to', 'quantity', 'transfer_pre_quantity', 'transfer_post_quantity', 'received_pre_quantity', 'received_post_quantity', 'transfer_by', 'reason'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'transfer_by', 'id');
    }
}
