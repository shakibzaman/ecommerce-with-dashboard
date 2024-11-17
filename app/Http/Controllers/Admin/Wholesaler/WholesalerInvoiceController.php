<?php

namespace App\Http\Controllers\Admin\Wholesaler;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WholesalerInvoiceController extends Controller
{
    public function create()
    {
        $store_type = ['cold_storage_quantity' => 'Cold Storage', 'office_quantity' => 'Office'];
        $store_type = ['' => '-- Please Select One --'] + $store_type;
        $suppliers = User::where('type', config('app.transaction_payable_type.wholesaler'))->get()->pluck('name', 'id')->prepend('-- Please Select One --', '');
        $products = Product::all();
        return view('wholesalers.invoices.create', compact('suppliers', 'products', 'store_type'));
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.buying_price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payable_amount' => 'required|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'due' => 'required|numeric|min:0',
        ]);

        // Begin transaction to ensure all updates happen atomically
        DB::beginTransaction();

        try {
            // Store supplier invoice details
            $invoice = Invoice::create([
                'supplier_id' => $request->supplier_id,
                'store_id' => $request->store_id,
                'total' => $request->total,
                'discount' => $request->discount,
                'payable_amount' => $request->payable_amount,
                'paid' => $request->paid,
                'due' => $request->due,
                'created_by' => auth()->id(),
            ]);

            // Loop through each product and process the invoice details
            foreach ($request->products as $product) {
                $productModel = Product::where('id', $product['product_id'])->where('store_id', $request->store_id)->first();

                // Check if the product is available in the selected store
                if (!$productModel) {
                    throw new \Exception('Product not found in the selected store.');
                }

                // Check if there's enough stock in the selected store
                if ($productModel->stock < $product['quantity']) {
                    throw new \Exception('Insufficient stock for product: ' . $productModel->name);
                }

                // Deduct product quantity from the store's stock
                $productModel->decrement('stock', $product['quantity']);

                // Store each product details in the InvoiceProduct
                InvoiceProduct::create([
                    'supplier_invoice_id' => $invoice->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'buying_price' => $product['buying_price'],
                    'total_price' => $product['quantity'] * $product['buying_price'],
                ]);
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('supplier.invoice.index')->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction in case of errors
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
