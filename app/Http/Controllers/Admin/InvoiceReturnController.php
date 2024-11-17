<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function App\Helpers\createStockHistory;
use function App\Helpers\createTransaction;

class InvoiceReturnController extends Controller
{


    public function invoiceProductReturnUpdate(Request $request)
    {
        DB::beginTransaction();
        $invoice_id = $request->invoice_id;
        $return_quantities = $request->return_quantity;
        $invoice = Invoice::where('id', $invoice_id)->first();
        $final_deduct_amount = 0;
        $total_quantity = 0;
        $total_amount = 0;
        try {
            // Loop through the products to update their return quantities
            foreach ($return_quantities as $product_id => $quantity) {
                if ($quantity != null) {
                    $total_quantity += $quantity;

                    $invoiceProduct = InvoiceProduct::where('invoice_id', $invoice_id)
                        ->where('product_id', $product_id)
                        ->first();
                    info('Invoice Product =>', [$invoiceProduct]);
                    // Update Invoice
                    if ($invoiceProduct) {
                        $previous_invoice_total = $invoiceProduct->total_price;
                        $previous_quantity = $invoiceProduct->quantity;
                        // Update the product's return quantity
                        $invoiceProduct->quantity -= $quantity;
                        $invoiceProduct->total_price = $invoiceProduct->quantity * $invoiceProduct->unit_price;
                        $invoiceProduct->save();

                        info('previous_invoice_total Amount is ', [$previous_invoice_total]);
                        info(' invoice_total Amount is ', [$invoiceProduct->total_price]);
                        $final_deduct_amount += ($previous_invoice_total -  $invoiceProduct->total_price);
                        info('Deduct Amount is ', [$previous_invoice_total -  $invoiceProduct->total_price]);
                        info('Final Deduct Amount', [$final_deduct_amount]);

                        info('Update Invoice Product =>', [$invoiceProduct]);
                    };
                    $total_amount += $invoiceProduct->total_price;
                    // Update Product Quantity
                    $product = Product::where('id', $product_id)->first();

                    // if ($invoice->store_id  == config('app.store_type.cold_storage')) {
                    //     $previous_product_quantity = $product->cold_storage_quantity;
                    //     $product->cold_storage_quantity -= $quantity;
                    //     $updated_product_quantity = $product->cold_storage_quantity;
                    // } else {
                    //     $previous_product_quantity = $product->office_quantity;
                    //     $product->office_quantity -= $quantity;
                    //     $updated_product_quantity = $product->office_quantity;
                    // }

                    if ($invoice->invoice_type == config('app.transaction_payable_type.supplier')) {
                        if ($invoice->store_id  == config('app.store_type.cold_storage')) {
                            $previous_product_quantity = $product->cold_storage_quantity;
                            $product->cold_storage_quantity -= $quantity;
                            $updated_product_quantity = $product->cold_storage_quantity;
                        } else {
                            $previous_product_quantity = $product->office_quantity;
                            $product->office_quantity -= $quantity;
                            $updated_product_quantity = $product->office_quantity;
                        }

                        $stock_history_note = 'Product Return to Supplier. Quantity is  ' . $quantity . ' . Reason is ' . $request->note . ". Return By " . Auth::user()->id . " Invoice ID is " . $invoice_id;
                    }
                    if ($invoice->invoice_type == config('app.transaction_payable_type.wholesaler')) {
                        if ($invoice->store_id  == config('app.store_type.cold_storage')) {
                            $previous_product_quantity = $product->cold_storage_quantity;
                            $product->cold_storage_quantity += $quantity;
                            $updated_product_quantity = $product->cold_storage_quantity;
                        } else {
                            $previous_product_quantity = $product->office_quantity;
                            $product->office_quantity += $quantity;
                            $updated_product_quantity = $product->office_quantity;
                        }
                        $stock_history_note = 'Product Return from Wholesaler. Quantity is  ' . $quantity . ' . Reason is ' . $request->note . ". Return By " . Auth::user()->id . " Invoice ID is " . $invoice_id;
                    }
                    $product->save();

                    // Stock History Created

                    $fromStockHistory = [
                        'store_id' => $invoice->store_id,
                        'quantity' =>  $quantity,
                        'product_id' => $product_id,
                        'invoice_product_id' => $invoice_id,
                        'previous_qty' => $previous_product_quantity,
                        'update_qty' => $updated_product_quantity,
                        'note' => $stock_history_note,
                    ];
                    createStockHistory($fromStockHistory);
                }
            }

            // Invoice Regenerate 
            $invoice->total = $total_amount;
            $invoice->payable_amount = $total_amount - $invoice->discount;
            $invoice->due = $total_amount - $invoice->paid;
            $invoice->save();

            // Update Ammount of User Account
            $userId = Auth::user()->id;
            if ($invoice->invoice_type == config('app.transaction_payable_type.supplier')) {
                $transaction_type = 'purchase';
                handleUserAccountPayment($invoice->user_id, $final_deduct_amount, 'add', $userId, $invoice_id);
                $note = 'Product Return to Supplier. Quantity is ' . $total_quantity . '. Reason is ' . $request->note . ". Return By " . $userId . " Invoice ID is " . $invoice_id;
            }
            if ($invoice->invoice_type == config('app.transaction_payable_type.wholesaler')) {
                $transaction_type = 'sell';
                handleUserAccountPayment($invoice->user_id, $final_deduct_amount, 'add', $userId, $invoice_id);
                $note = 'Product Return to Wholesaler. Quantity is ' . $total_quantity . '. Reason is ' . $request->note . ". Return By " . $userId . " Invoice ID is " . $invoice_id;
            }
            info('Total Amount', [$total_amount]);
            // Create a Transaction history
            createTransaction($invoice->user_id, $invoice->invoice_type, $final_deduct_amount, $transaction_type, $userId, $note);
            DB::commit();
            return redirect()->back()->with('success', 'Product Return Successfully');
            return ['status' => 200, 'message' => 'Product Return Successfully'];
        } catch (Exception $e) {
            info('Error while Return Product', [$e]);
            DB::rollBack();
            return redirect()->back()->with('error', 'Product Return Failled');

            return ['status' => 400, 'message' => 'Product Return Failled'];
        }
    }
}
