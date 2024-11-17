<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierSheet extends Model
{
    use HasFactory;

    public function sheets()
    {
        return $this->hasMany(CourierSheetOrder::class, 'courier_sheet_id', 'id');
    }
    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id', 'id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
