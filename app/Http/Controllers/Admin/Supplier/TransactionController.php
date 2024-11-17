<?php

namespace App\Http\Controllers\Admin\Supplier;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function show($id)
    {
        $supplier = User::with('account', 'transactions', 'transactions.creator')->where('id', $id)->first();
        return view('suppliers.transactions.index', compact('supplier'));
    }
}
