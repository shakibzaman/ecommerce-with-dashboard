<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['customer_id', 'address', 'store_id', 'total', 'discount', 'payable_amount', 'paid', 'due', 'status_id', 'delivery_charge', 'delivery_company_id', 'created_by', 'note'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }
    public function delivery()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id', 'id');
    }
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function payment()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'id')->where('payable_type', config('app.transaction_payable_type.customer'))->orderBy('id', 'desc');
    }
}
