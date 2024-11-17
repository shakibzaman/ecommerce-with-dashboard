<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'unit_id',
        'price',
        'discount',
        'wholesell_price',
        'image',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });

        static::updating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    public function histories()
    {
        return $this->hasMany(StockHistory::class, 'product_id', 'id')->orderBy('id', 'desc');
    }
    public function transferLogs()
    {
        return $this->hasMany(ProductTransferLog::class, 'product_id', 'id')->orderBy('id', 'desc');
    }
}
