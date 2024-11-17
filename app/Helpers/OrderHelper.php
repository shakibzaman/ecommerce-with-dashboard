<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusLog;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

function handleStatus($order_id, $status_id, $user_id)
{
    info('Order helper order_id', [$order_id]);
    info('Order helper status_id', [$status_id]);
    $order = Order::with('details')->where('id', $order_id)->first();
    if (!$order) {
        info('Order not found', [$order_id]);
        return ['status' => 400, 'message' => 'Order not found'];
    }
    $order_details = $order->details;
    info('Main Order details', [$order_details]);
    info(' Handle Status Order is', [$order]);
    $order_status = (int) $order->status_id;
    $requested_status_id = (int) $status_id;
    // info('Order sta', [$order_status]);
    // info('Req sta', [$requested_status_id]);
    if ($order_status == $requested_status_id) {
        return ['status' => 200, 'message' => 'No Status Changed'];
    }
    DB::beginTransaction();
    try {
        if (in_array($requested_status_id, [config('status.pending'), config('status.cancel'), config('status.return')])) {
            // If already deduct product quantity
            if (in_array($order_status, [config('status.packaging'), config('status.shipped'), config('status.delivered')])) {
                // Increment Product Quantity to main store
                info('Order details', [$order_details]);
                foreach ($order_details as $detail) {
                    $increment = changeProductQuantity($detail, $order->store_id, 'add');
                    if ($increment['status'] == 400) {
                        DB::rollBack();

                        $message = $increment['message'];
                        return ['status' => 400, 'message' => 'Error changeProductQuantity order: ' . $message];
                    }

                    info('Increment is', [$increment]);
                }
            }
        }

        if (in_array($requested_status_id, [config('status.packaging'), config('status.shipped'), config('status.delivered')])) {
            if (in_array($order_status, [config('status.pending'), config('status.cancel'), config('status.return')])) {

                // Check if product Available or not 

                $checkStock =  handleOrderCheckStock($order_id, $order->store_id);
                if (count($checkStock) > 0) {
                    $products = Product::whereIn('id', $checkStock)->get();
                    return ['status' => 400, 'error' => 'Stock is unavailable for Sell', 'data' => $products];
                }
                // Decrement Product Quantity to main store
                info('Order details', [$order_details]);
                foreach ($order_details as $detail) {
                    $decrement = changeProductQuantity($detail, $order->store_id, 'sub');
                    if ($decrement['status'] == 400) {
                        DB::rollBack();
                        $message = $decrement['message'];
                        return ['status' => 400, 'message' => 'Error changeProductQuantity order: ' . $message];
                    }
                    info('Decrement is', [$decrement]);
                }
            }
        }

        $order->status_id = $requested_status_id;
        $order->save();

        $status_log =  handleOrderStatusChangeLog($order_id, $status_id, $user_id);

        if ($status_log['status'] != 200) {
            DB::rollBack();
            return ['status' => 400, 'message' => 'Error saving order Status Log: '];
        }
        DB::commit();
        info('Order final after save', [$order]);
        return ['status' => 200, 'message' => 'Order Status Update'];
    } catch (\Exception $e) {
        DB::rollBack();
        info('Error saving order', [$e->getMessage()]);
        return ['status' => 400, 'message' => 'Error saving order: ' . $e->getMessage()];
    }
}

function handleDeliveryCompany($order_id, $company_id)
{

    $order = Order::with('details')->where('id', $order_id)->first();
    if (!$order) {
        info('Order not found', [$order_id]);
        return ['status' => 400, 'message' => 'Order not found'];
    }
    $orderDeliveryCompanyId = $order->delivery_company_id;
    $requestedCompanyId = $company_id;

    if ($orderDeliveryCompanyId == $requestedCompanyId) {
        return ['status' => 200, 'message' => 'No Delivery Company Changed'];
    }
    DB::beginTransaction();
    try {
        $order->delivery_company_id = $requestedCompanyId;
        $order->save();
        DB::commit();
        return ['status' => 200, 'message' => 'Delivery Company Update'];
        info('Order final after save', [$order]);
    } catch (\Exception $e) {
        DB::rollBack();
        info('Error saving order', [$e->getMessage()]);
        return ['status' => 400, 'message' => 'Error saving order: ' . $e->getMessage()];
    }
}
function handleDueOrderPayment($order_id, $payment, $userId)
{
    $order = Order::with('details')->where('id', $order_id)->first();
    if (!$order) {
        info('Order not found', [$order_id]);
        return ['status' => 400, 'message' => 'Order not found'];
    }
    DB::beginTransaction();
    try {
        info('Starting Time Order is', [$order]);
        // Order paid amount add and due deduct
        $order->paid += $payment;
        $order->due -= $payment;
        $updateOrder = $order->save();
        info('updateOrder', [$updateOrder]);
        $customize_note = $payment . ' Tk paid for Customer Order';
        if ($updateOrder) {
            // add Payment

            $payment_history_store =  handlePayment($order->customer_id, config('app.transaction_payable_type.customer'), $payment, config('app.transaction_type.sell'), 'credit', 'cash', $order->id, $customize_note, $userId, now());
            info('payment data is', [$payment_history_store]);
            if (!$payment_history_store) {
                info('payment data Error ', [$payment_history_store]);
                DB::rollBack();
                return ['status' => 400, 'message' => 'Payment Insert error '];
            }
            // Add Transaction

            $createTransactionHistory =  createTransaction($order->customer_id, config('app.transaction_payable_type.customer'), $payment, config('app.transaction_type.sell'), $userId, $customize_note);
            info('transaction data is', [$createTransactionHistory]);

            if (!$createTransactionHistory) {
                info('createTransactionHistory error', [$createTransactionHistory]);
                DB::rollBack();
                return ['status' => 400, 'message' => 'Transaction Insert error'];
            }
        }

        DB::commit();
        return ['status' => 200, 'message' => 'Payment Update'];
    } catch (\Exception $e) {
        DB::rollBack();
        info('Error Due Payment order', [$e]);
        return ['status' => 400, 'message' => 'Error saving order: ' . $e->getMessage()];
    }
}
function changeProductQuantity($detail, $store_name, $type)
{
    DB::beginTransaction();
    try {

        $product = Product::where('id', $detail->product_id)->first();
        $previous_qty = $product->$store_name;
        if ($type == 'add') {
            $product->$store_name += $detail->quantity;
        } else {
            $product->$store_name -= $detail->quantity;
        }
        $product->save();


        // Add Product History

        $historyData['store_id'] =  $store_name;
        $historyData['quantity'] =  $detail->quantity;
        $historyData['product_id'] =  $detail->product_id;
        $historyData['order_id'] =  $detail->order_id;
        $historyData['previous_qty'] =  $previous_qty;
        $historyData['update_qty'] =  $product->$store_name;
        $historyData['note'] =  $detail->quantity . ' Quantity stock ' . $type . ($type == 'add' ? ' From ' : ' To ') . $store_name;
        createStockHistory($historyData);
        DB::commit();

        return ['status' => 200, 'message' => 'Product Stock Change'];
    } catch (Exception $e) {
        DB::rollBack();
        info('Error while Product Stock Change', [$e->getMessage()]);
        return ['status' => 400, 'message' => 'Error while Product Stock Change' . $e->getMessage()];
    }
}

function handleOrderStatusChangeLog($order_id, $status_id, $changed_by)
{
    try {
        $change = OrderStatusLog::create([
            'order_id' => $order_id,
            'status_id' => $status_id,
            'changed_by' => $changed_by,
        ]);
        return ['status' => 200, 'message' => 'Order Status Log Stored'];
    } catch (Exception $e) {
        info('Exception for status change', [$e]);
        return ['status' => 400, 'message' => 'Error while Order Statuc Change' . $e->getMessage()];
    }
}

function cartTotal()
{
    $carts = session()->get('cart', []);
    $totalQuantity = 0;
    $totalPrice = 0;
    foreach ($carts as $item) {
        $totalQuantity += $item['quantity'];
        $totalPrice += $item['price'] * $item['quantity'];
    }
    return ['totalQuantity' => $totalQuantity, 'totalPrice' => $totalPrice];
}
