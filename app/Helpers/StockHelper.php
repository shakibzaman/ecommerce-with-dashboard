<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductTransferLog;
use App\Models\StockHistory;
use Exception;

function createStockHistory($data)
{
    try {
        $history_create =  StockHistory::create($data);
        if ($history_create) {
            return ['status' => 200, 'message' => 'History Created Successfully'];
        }
    } catch (Exception $e) {
        info('Stock History Creating error', [$e->getMessage()]);
        return ['status' => 400, 'message' => 'History Created Error'];
    }
}

function createProductTransferLog($data)
{
    try {
        $log_create = ProductTransferLog::create($data);
        if ($log_create) {
            return ['status' => 200, 'message' => 'Product Transfer Log Created Successfully'];
        }
    } catch (Exception $e) {
        info('Stock Transfer Log Creating error', [$e]);
        return ['status' => 400, 'message' => 'Product Transfer Created Error'];
    }
}

// function handleOrderCheckStock($order_id, $store)
// {
//     $orderDetails = OrderDetail::with('product')->where('order_id', $order_id)->get();
//     $unavailable_product_id = [];

//     foreach ($orderDetails as $detail) {
//         info('Stock quantity is', [$detail->product->$store]);
//         info('Requested quantity is', [$detail['quantity']]);
//         if (($detail->product->$store) < (int)$detail['quantity']) {
//             $unavailable_product_id[] = $detail['product_id'];
//             info('Product is ', [$detail->product]);
//         }
//     }
//     return $unavailable_product_id;
// }

function handleOrderCheckStock($order_id, $store)
{
    // Retrieve all order details with product info
    $orderDetails = OrderDetail::with('product')->where('order_id', $order_id)->get();
    $unavailable_product_ids = [];

    foreach ($orderDetails as $detail) {
        // Make sure $store corresponds to the correct column in the product table
        $product_stock = $detail->product->$store ?? 0;  // Default to 0 if not found

        // Log stock and requested quantity for debugging (optional)
        info('Stock quantity is', [$product_stock]);
        info('Requested quantity is', [$detail['quantity']]);

        // Check if the store has enough quantity for the order
        if ((int)$product_stock < (int)$detail['quantity']) {
            // Add the unavailable product ID to the array
            $unavailable_product_ids[] = $detail['product_id'];
            info('Product is unavailable', [$detail->product]);
        }
    }

    // Return the list of unavailable product IDs (if any)
    return $unavailable_product_ids;
}
