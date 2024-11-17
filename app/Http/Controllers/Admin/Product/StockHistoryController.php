<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;

class StockHistoryController extends Controller
{
    public function stockHistory($id)
    {
        $product = Product::with('unit')->where('id', $id)->first();
        $histories = $product->histories()->paginate(20);
        return view('products.stock.history', compact('product', 'histories'));
    }
    public function transferLog($id)
    {
        $product = Product::with('unit')->where('id', $id)->first();
        $logs = $product->transferLogs()->with('creator')->paginate(20);
        return view('products.stock.logs', compact('product', 'logs'));
    }
}
