<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;
    protected $fillable = ['store_id', 'quantity', 'product_id', 'invoice_product_id', 'order_id', 'previous_qty', 'update_qty', 'note'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
