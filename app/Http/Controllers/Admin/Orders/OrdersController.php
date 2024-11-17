<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Events\OrderProcessed;
use App\Http\Controllers\Controller;
use App\Jobs\CourierSheetOrderMakeJob;
use App\Jobs\OrderStatusChangeJob;
use App\Models\CourierSheet;
use App\Models\CourierSheetOrder;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\Product;
use App\Models\Status;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

use function App\Helpers\getCurrentUserId;
use function App\Helpers\handleDeliveryCompany;
use function App\Helpers\handleDueorderPayment;
use function App\Helpers\handleStatus;

class OrdersController extends Controller
{
    public function index()
    {
        $statuses = Status::pluck('name', 'id')->prepend('Select Status', '');
        $delivery_companies = DeliveryCompany::pluck('name', 'id')->prepend('Select Company', '');;

        $orders = Order::with('customer', 'status', 'details.product', 'payment')->orderBy('id', 'desc')->get();
        return view('orders.index', compact('orders', 'statuses', 'delivery_companies'));
    }

    public function test()
    {
        $userId = getCurrentUserId();
        logger('User id==>', [$userId]);
    }
    public function createOrder()
    {
        $products = Product::all();
        $statuses = Status::all();
        $delivery_companies = DeliveryCompany::all();
        return view('orders.create', compact('products', 'statuses', 'delivery_companies'));
    }

    public function indexTest()
    {
        return view('orders.orders');
    }

    public function listCourierOrderSheet()
    {
        $courier_sheets = CourierSheet::with('sheets.order', 'sheets.order.customer', 'company', 'creator')->orderBy('id', 'desc')->paginate(20);
        return view('courier_sheet.index', compact('courier_sheets'));
    }
    public function editOrder($id)
    {
        $order = Order::with('details.product')->where('id', $id)->first();
        $products = Product::all();
        $statuses = Status::all();
        $delivery_companies = DeliveryCompany::all();
        return view('orders.edit', compact('products', 'statuses', 'delivery_companies', 'order'));
    }

    public function createCourierOrderSheet()
    {
        $statuses = Status::all();
        $delivery_companies = DeliveryCompany::all();
        return view('orders.courier_sheet', compact('statuses', 'delivery_companies'));
    }
    public function storeCourierOrderSheet(Request $request)
    {
        DB::beginTransaction();
        try {
            $courier_sheet = new CourierSheet();
            $courier_sheet->invoice = $request->invoice;
            $courier_sheet->delivery_company_id = $request->delivery_company_id;
            $courier_sheet->created_by = Auth::user()->id;
            $courier_sheet->save();
            $jobs = [];
            foreach ($request->orders as $key => $order) {
                $courier_sheet_make = CourierSheetOrderMakeJob::dispatch($courier_sheet->id, $order);
                if ($courier_sheet_make) {
                    // Collect all the jobs 
                    $jobs[] = new OrderStatusChangeJob($order, config('status.shipped'), Auth::id());
                }
            }

            if (!empty($jobs)) {
                Bus::chain(array_merge($jobs), function () use ($courier_sheet) {
                    event(new OrderProcessed($courier_sheet->id));
                    logger('All jobs finished, event dispatched for CourierSheet ID: ' . $courier_sheet->id);
                })
                    ->catch(function ($e) {
                        logger("Job Chain Failled", [$e->getMessage()]);
                    })
                    ->dispatch();
            }

            DB::commit();
            return ['status' => 200, 'message' => 'Courier Sheet Added'];
        } catch (Exception $e) {
            DB::rollBack();
            info('Courier Sheet Added Error ', [$e->getMessage()]);
            return ['status' => 400, 'message' => 'Courier Sheet Added Error ' . $e->getMessage()];

            return $e->getMessage();
        }
    }

    public function searchOrder(Request $request)
    {
        $searchTerm = $request->input('search');

        // Fetch orders based on the search term

        $order = Order::with('customer', 'status')->where('id', $searchTerm)
            ->first();
        if ($order->status_id != config('status.packaging')) {
            return ['status' => 400, 'message' => 'Sorry !!! Only Packaging Order can shipped'];
        }
        if ($order) {
            // return ['status' => 200, 'message' => 'You can Order shipped', 'data' => response()->json($order)];

            // Return JSON response
            return response()->json($order);
        }
    }
    public function orderStatusUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Validate paid amount vs due
            if ($request->due && $request->due < $request->newPaid) {
                return response()->json(['status' => 400, 'message' => 'You cannot pay more than the due amount'], 400);
            }

            // Update Order Status
            if ($request->status_id) {
                $statusResponse = handleStatus($id, $request->status_id, Auth::id());
                if ($statusResponse['status'] != 200) {
                    DB::rollBack();
                    return response()->json($statusResponse, 400);
                }
            }

            // Update Delivery Company
            if ($request->delivery_company_id) {
                $deliveryResponse = handleDeliveryCompany($id, $request->delivery_company_id);
                if ($deliveryResponse['status'] != 200) {
                    DB::rollBack();
                    return response()->json($deliveryResponse, 400);
                }
            }

            // Handle Payment
            if ($request->newPaid != null && $request->newPaid > 0) {
                $paymentResponse = handleDueOrderPayment($id, $request->newPaid, Auth::id());
                if ($paymentResponse['status'] != 200) {
                    DB::rollBack();
                    return response()->json($paymentResponse, 400);
                }
            }

            DB::commit();
            return response()->json(['status' => 200, 'message' => 'Order updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 400, 'message' => 'Error updating order: ' . $e->getMessage()], 400);
        }
    }

    // public function orderStatusUpdate(Request $request, $id)
    // {
    //     DB::beginTransaction();

    //     try {

    //         if ($request->due && $request->due < $request->newPaid) {
    //             return ['status' => 400, 'message' => 'Sorry you can not paid more then you have due'];
    //         }

    //         if ($request->status_id) {
    //             $statusUpdate =  handleStatus($id, $request->status_id);
    //             info('statusUpdate', [$statusUpdate]);
    //             // if ($statusUpdate['status'] != 200) {
    //             //     return ['status' => 400, 'message' => 'Order status Updated error', 'data' => $statusUpdate['data'] ?? ''];
    //             // }
    //         }
    //         logger('Order status End');
    //         if ($request->delivery_company_id) {
    //             $deliveryCompanyUpdate =  handleDeliveryCompany($id, $request->delivery_company_id);
    //             info('deliveryCompanyUpdate', [$deliveryCompanyUpdate]);

    //             // if ($deliveryCompanyUpdate['status'] != 200) {
    //             //     return ['status' => 400, 'message' => 'Order delivery Company Updated error'];
    //             // }
    //         }
    //         logger('Order delivery End');

    //         if ($request->newPaid != null && $request->newPaid > 0) {
    //             $userId = Auth::user()->id;
    //             $paymentUpdate =  handleDueOrderPayment($id, $request->newPaid, $userId);
    //             info('paymentUpdate', [$paymentUpdate]);

    //             // if ($paymentUpdate['status'] != 200) {
    //             //     return ['status' => 400, 'message' => 'Order Payment Updated error'];
    //             // }
    //         }
    //         logger('Order Payment End');
    //         DB::commit();
    //         return ['status' => 200, 'message' => 'Successfully updated order'];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         info('Error in order update', [$e->getMessage()]);
    //         return ['status' => 400, 'message' => 'Error updating order: ' . $e->getMessage()];
    //     }
    // }
}
