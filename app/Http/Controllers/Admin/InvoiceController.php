<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\UserAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function App\Helpers\createStockHistory;
use function App\Helpers\createTransaction;
use function App\Helpers\handleOrderStatusChangeLog;

class InvoiceController extends Controller
{
    public function supplierInvoiceCreate()
    {
        $store_type = ['cold_storage_quantity' => 'Cold Storage', 'office_quantity' => 'Office Quantity'];
        $store_type = ['' => '-- Please Select One --'] + $store_type;
        $suppliers = User::where('type', 3)->get()->pluck('name', 'id')->prepend('-- Please Select One --', '');
        $products = Product::all()->pluck('name', 'id')->prepend('-- Please Select One --', '');
        return view('suppliers.invoices.create', compact('suppliers', 'products', 'store_type'));
    }

    public function productStore(Request $request)
    {
        $validatedData = $request->validate([
            'invoice_type' => 'required',
            'store_id' => 'required',
            'user_id' => 'required|exists:users,id',
            'date' => 'nullable|date',
            'total' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'payable_amount' => 'required|numeric',
            'paid' => 'nullable|numeric',
            'due' => 'required|numeric',
            'created_by' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:200',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric',
            'products.*.unit_price' => 'required|numeric',
        ]);
        // $validator = Validator::make($request->all(), [
        //     'invoice_type' => 'required',
        //     'store_id' => 'required',
        //     'user_id' => 'required|exists:users,id',
        //     'date' => 'nullable|date',
        //     'total' => 'required|numeric',
        //     'discount' => 'nullable|numeric',
        //     'payable_amount' => 'required|numeric',
        //     'paid' => 'nullable|numeric',
        //     'due' => 'required|numeric',
        //     'created_by' => 'required|exists:users,id',
        //     'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:200',
        //     'products.*.product_id' => 'required|exists:products,id',
        //     'products.*.quantity' => 'required|numeric',
        //     'products.*.unit_price' => 'required|numeric',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }
        DB::beginTransaction();
        try {
            if ($request->hasFile('image')) {
                logger('Image is available');
                $imageName = 'invoice-' . time() . '.' . $request->image->extension();
                $request->image->move(public_path('images/suppliers/invoices'), $imageName);
                $validatedData['image'] = $imageName;
            }
            $invoice = Invoice::create([
                'invoice_id' => $request->input('invoice_id') ?? now()->timestamp,
                'invoice_type' => $request->input('invoice_type'),
                'store_id' => $request->input('store_id'),
                'user_id' => $request->input('user_id'),
                'date' => $request->input('date') ?? now(),
                'total' => $request->input('total'),
                'discount' => $request->input('discount') ?? 0,
                'payable_amount' => $request->input('payable_amount'),
                'paid' => $request->input('paid') ?? 0,
                'due' => $request->input('due'),
                'created_by' => $request->input('created_by'),
                'image' => $request->file('image') ?  $imageName : null,
            ]);
            foreach ($request->input('products', []) as $product) {
                $invoice_add_product = InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total_price' => $product['unit_price'] * $product['quantity'],
                ]);
                if ($invoice_add_product) {
                    $product_detail = Product::where('id', $product['product_id'])->first();
                    if ($product_detail) {
                        $previous_qty = $product_detail->cold_storage_quantity;
                        if ($request->input('store_id') == config('app.store_type.cold_storage')) {
                            $product_detail->cold_storage_quantity += $product['quantity'];
                        } else {
                            $previous_qty = $product_detail->office_quantity;
                            $product_detail->office_quantity += $product['quantity'];
                        }
                        $product_data_update = $product_detail->save();
                        if ($product_data_update) {
                            $data = [
                                'store_id' => $request->input('store_id'),
                                'quantity' => $product['quantity'],
                                'invoice_product_id' => $invoice_add_product->id,
                                'product_id' => $product['product_id'],
                                'previous_qty' => $previous_qty,
                                'update_qty' => $previous_qty + $product['quantity'],
                                'note' => 'New Stock from Supplier'
                            ];
                            $history_created = createStockHistory($data);
                            info('History Created ', [$history_created]);
                            if ($history_created['status'] != 200) {
                                DB::rollBack();
                                return redirect()->back()->with('error', $history_created['message']);
                            }
                        }
                    }
                }
                info('invoice_add_product is', [$invoice_add_product]);
            }


            // add Supplier Account amount 
            if ($request->input('due') > 0) {
                $userId = Auth::user()->id;
                $userPayment = handleUserAccountPayment($request->input('user_id'), $request->input('due'), 'due', $userId, $request->input('invoice_id'));
                if (!$userPayment) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'No Supplier Account found');
                }
            }

            // Add Payment 
            if ($request->input('paid') > 0) {
                $payment = handlePayment(
                    $request->input('user_id'),
                    config('app.transaction_payable_type.supplier'), // Payable type (e.g., 2 for suppliers)
                    $request->input('paid'),
                    'purchase', // Transaction type
                    'debit', // Payment type
                    'cash', // Payment method
                    $invoice->id,
                    $request->input('paid') . 'Tk paid for product purchased. Invoice id ' . $request->input('invoice_id'),
                    Auth::user()->id,
                    $request->input('date'),
                );
                info('payment data is', [$payment]);
                if (!$payment) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Payment Insert error');
                }
            }
            DB::commit();
        } catch (Exception $e) {
            info('Supplier Product Create Error', [$e]);
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
            return $e;
        }
        return redirect()->route('supplier.invoices.list', ['id' => $request->input('user_id')])->with('success', 'Invoice created successfully.');
    }

    public function invoiceList($supplier_list)
    {
        $supplier = User::with('account', 'supplier_invoices.products', 'supplier_invoices.creator')->where('id', $supplier_list)->first();
        return view('suppliers.invoices.index', compact('supplier'));
    }
    public function invoiceProductList($invoice_id)
    {
        $products = InvoiceProduct::with('product')->where('invoice_id', $invoice_id)->get();
        return view('suppliers.invoices.modal.product', compact('products'));
    }
    public function invoiceProductListReturn($invoice_id)
    {
        $products = InvoiceProduct::with('product')->where('invoice_id', $invoice_id)->get();
        return view('invoices.modal.product-return', compact('products', 'invoice_id'));
    }

    public function supplierinvoiceList()
    {
        $orders = Invoice::with('causer', 'products')->where('invoice_type', config('app.user_type.supplier'))->get();
        $order_type = config('app.user_type.supplier');
        return view('invoices.orders.index', compact('orders', 'order_type'));
    }

    public function paymentList($user_id)
    {
        $payments = Payment::with('creator')->where('payable_id', $user_id)->orderBy('id', 'desc')->paginate(20);
        return view('suppliers.payments.index', compact('payments'));
    }



    public function WholesaleProductstore(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payable_amount' => 'required|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'due' => 'required|numeric|min:0',
        ]);
        $checkStock =  $this->checkStock($request);
        if (count($checkStock) > 0) {
            $products = Product::whereIn('id', $checkStock)->get();
            return ['status' => 400, 'message' => 'Stock is unavailable for Sell', 'data' => $products];
        }
        // Begin transaction to ensure all updates happen atomically
        DB::beginTransaction();
        // Check Product Available or not in stock 
        try {
            // Store supplier invoice details
            $invoice = Invoice::create([
                'user_id' => $request->user_id,
                'invoice_id' => now()->timestamp,
                'invoice_type' => config('app.transaction_payable_type.wholesaler'),
                'date' => now(),
                'store_id' => $request->store_id,
                'total' => $request->total,
                'discount' => $request->discount ?? 0,
                'payable_amount' => $request->payable_amount,
                'paid' => $request->paid ?? 0,
                'due' => $request->due,
                'created_by' => Auth::user()->id,
            ]);
            info('Create Wholesaler Invoice ', [$invoice]);
            $store_qty = $request->store_id;
            // Loop through each product and process the invoice details
            foreach ($request->products as $product) {
                $productModel = Product::where('id', $product['product_id'])->first();

                info('Product Stock is', [$store_qty]);
                // Deduct product quantity from the store's stock
                if ($store_qty == 'cold_storage_quantity') {
                    $previous_qty = $productModel->cold_storage_quantity;

                    $productModel->decrement('cold_storage_quantity', $product['quantity']);
                    $update_qty = $productModel->cold_storage_quantity;
                } else {
                    $previous_qty = $productModel->office_quantity;
                    $productModel->decrement('office_quantity', $product['quantity']);
                    $update_qty = $productModel->office_quantity;
                }

                logger('Product Decrement done');

                // Store each product details in the SupplierInvoiceProduct
                $invoiceProductStore = InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total_price' => $product['quantity'] * $product['unit_price'],
                ]);
                info('invoiceProductStore', [$invoiceProductStore]);
                // Create Product Stock log 
                $historyData = [
                    'store_id' => $request->store_id == 'cold_storage_quantity' ? 1 : 2,
                    'quantity' => $product['quantity'],
                    'product_id' => $product['product_id'],
                    'invoice_product_id' => $invoice->id,
                    'previous_qty' => $previous_qty,
                    'update_qty' => $update_qty,
                    'note' => 'Product Sell to Wholesaller'
                ];
                $history_created = createStockHistory($historyData);
                info('History Created ', [$history_created]);
                if ($history_created['status'] != 200) {
                    DB::rollBack();
                    return ['status' => 405, 'message' => 'error' . $history_created['message']];
                }
            }


            // Add Payment 
            if ($request->input('paid') > 0) {
                $payment = handlePayment(
                    $request->input('user_id'),
                    config('app.transaction_payable_type.wholesaler'), // Payable type (e.g., 4 for Wholesaler)
                    $request->input('paid'),
                    'sell', // Transaction type
                    'credit', // Payment type
                    'cash', // Payment method
                    $invoice->id,
                    $request->input('paid') . 'Tk paid for product WholeSale. Invoice id ' . $invoice->id,
                    Auth::user()->id,
                    $request->input('date'),
                );
                info('payment data is', [$payment]);
                if (!$payment) {
                    DB::rollBack();
                    return ['status' => 405, 'message' => 'error ! Payment Insert error'];
                }
            }

            // add Supplier Account amount 
            if ($request->input('due') > 0) {
                $userId =  Auth::user()->id;
                $userPayment = handleUserAccountPayment($request->input('user_id'), $request->input('due'), 'due', $userId, $invoice->id);
                if (!$userPayment) {
                    DB::rollBack();
                    return ['status' => 405, 'message' => 'error ! No Supplier Account found'];
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();
            return ['status' => 200, 'message' => 'Stock for Sell'];

            return redirect()->route('supplier.invoice.index')->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            info('Error while store wholesaler order', [$e]);
            // Rollback the transaction in case of errors
            DB::rollBack();
            return ['status' => 405, 'message' => 'error' . $e->getMessage()];

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function orderStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'total' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'payable_amount' => 'required|numeric',
            'paid' => 'nullable|numeric',
            'due' => 'required|numeric',
            'created_by' => 'required|exists:users,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric',
            'products.*.unit_price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $checkStock =  $this->checkStock($request);
        if (count($checkStock) > 0) {
            $products = Product::whereIn('id', $checkStock)->get();
            return ['status' => 400, 'message' => 'Stock is unavailable for Sell', 'data' => $products];
        }
        // Begin transaction to ensure all updates happen atomically
        DB::beginTransaction();
        // Check Product Available or not in stock 
        try {
            // Check Customer 
            $checkCustomer = $this->checkCustomer($request);
            // Store supplier invoice details
            $invoice = Order::create([
                'customer_id' => $checkCustomer->id,
                'address' => $request->address,
                'invoice_id' => now()->timestamp,
                'invoice_type' => config('app.transaction_payable_type.customer'),
                'date' => now(),
                'store_id' => $request->store_id,
                'total' => (int) $request->total,
                'discount' => (int) $request->discount ?? 0,
                'payable_amount' => (int) $request->payable_amount,
                'paid' => (int) $request->paid ?? 0,
                'due' => (int)  $request->due,
                'status_id' => $request->status_id ?? 1,
                'note' => $request->note,
                'delivery_company_id' => $request->delivery_company_id ?? 3,
                'delivery_charge' => (int) $request->delivery_charge ?? 0,
                'created_by' => Auth::user()->id,
            ]);
            info('Create Customer Invoice ', [$invoice]);
            $store_qty = $request->store_id;
            // Loop through each product and process the invoice details
            foreach ($request->products as $product) {

                // Store each product details in the SupplierInvoiceProduct
                $invoiceProductStore = OrderDetail::create([
                    'order_id' => $invoice->id,
                    'product_id' => $product['product_id'],
                    'quantity' => (int)$product['quantity'],
                    'unit_price' => (int) $product['unit_price'],
                    'total_price' => (int) $product['quantity'] * (int) $product['unit_price'],
                ]);
                info('invoiceProductStore', [$invoiceProductStore]);
            }


            // Add Payment 
            if ($request->input('paid') > 0) {
                $payment = handlePayment(
                    $checkCustomer->id,
                    config('app.transaction_payable_type.customer'), // Payable type (e.g., 5 for Customer)
                    $request->input('paid'),
                    'sell', // Transaction type
                    'credit', // Payment type
                    'cash', // Payment method
                    $invoice->id,
                    $request->input('paid') . 'Tk paid for product Customer. Invoice id ' . $invoice->id,
                    Auth::user()->id,
                    $request->input('date'),
                );
                info('payment data is', [$payment]);
                if (!$payment) {
                    DB::rollBack();
                    return ['status' => 405, 'message' => 'error ! Payment Insert error'];
                }
            }
            $status_log =  handleOrderStatusChangeLog($invoice->id, 1,  Auth::user()->id);
            if ($status_log['status'] != 200) {
                DB::rollBack();
                return ['status' => 400, 'message' => 'Error saving order Status Log: '];
            }
            // Commit the transaction if everything is successful
            DB::commit();
            return ['status' => 200, 'message' => 'Stock for Sell'];

            return redirect()->route('supplier.invoice.index')->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            info('Error while store wholesaler order', [$e]);
            // Rollback the transaction in case of errors
            DB::rollBack();
            return ['status' => 405, 'message' => 'error' . $e->getMessage()];

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function orderUpdate(Request $request, $id)
    {
        // Fetch the existing order
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
            ], 404);
        }

        // Validate the request
        $request->validate([
            'total' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'payable_amount' => 'required|numeric',
            'paid' => 'nullable|numeric',
            'due' => 'required|numeric',
            'delivery_company_id' => 'nullable|exists:delivery_companies,id',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric',
            'products.*.unit_price' => 'required|numeric',
        ]);

        // Begin transaction to ensure all updates happen atomically
        DB::beginTransaction();

        try {
            // Update the order details
            $updateOrder = $order->update([
                'total' => (float) $request->total,
                'discount' => (float) $request->discount ?? 0,
                'payable_amount' => (float) $request->payable_amount,
                'paid' => (float) $request->paid ?? 0,
                'due' => (float) $request->due,
                'delivery_company_id' => $request->delivery_company_id ?? 1,
                'delivery_charge' => $request->delivery_charge,
                'updated_by' => Auth::user()->id,
            ]);

            // Delete Existance Order details 
            if ($updateOrder) {
                $orderDetails = OrderDetail::where('order_id', $id)->delete();
                if ($orderDetails) {

                    info('deleteOrders', [$orderDetails]);
                }

                // Add new order details 
                info('Count', [count($request->product_ids)]);
                for ($i = 0; $i < count($request->product_ids); $i++) {
                    logger($i);
                    logger('Product ID' . $request->product_ids[$i]);
                    $invoiceProductStore = OrderDetail::create([
                        'order_id' => $id,
                        'product_id' => $request->product_ids[$i],
                        'quantity' => (float) $request->quantities[$i],
                        'unit_price' => (float) $request->unit_price[$i],
                        'total_price' => (float) $request->quantities[$i] * (float) $request->unit_price[$i],
                    ]);
                    info('invoiceProductStore', [$invoiceProductStore]);
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();
            return redirect()->route('order.list')->with('success', 'Order Updated successfully.');

            return response()->json(['status' => 'success', 'message' => 'Order updated successfully!']);
        } catch (\Exception $e) {
            // Rollback the transaction in case of errors
            info('Order update error Exception', [$e]);
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error: ' . $e]);
        }
    }



    public function checkCustomer($request)
    {
        $phone = $request->phone;
        $user = User::where('phone', $phone)->first();
        if ($user) {
            return $user;
        } else {
            return $this->addCustomer($request);
        }
    }
    public function addCustomer($request)
    {
        $data['name'] = $request->name;
        $data['phone'] = $request->phone;
        $data['email'] = $request->phone;
        $data['password'] = bcrypt($request->phone);
        $data['type'] = config('app.user_type.customer');
        return $user = User::create($data);
    }
    public function checkStock($request)
    {
        $unavailable_product_id = [];
        $store_qty = $request->store_id;

        foreach ($request->products as $product) {
            $productModel = Product::where('id', $product['product_id'])->first();
            info('Stock quantity is', [$productModel->$store_qty]);
            info('Requested quantity is', [$product['quantity']]);
            if (($productModel->$store_qty) < (int)$product['quantity']) {
                $unavailable_product_id[] = $product['product_id'];
                info('Product is ', [$productModel]);
            }
        }

        return $unavailable_product_id;
    }
}
