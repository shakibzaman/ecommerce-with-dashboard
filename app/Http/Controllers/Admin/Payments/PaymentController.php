<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Imports\OrderPaymentImport;
use App\Jobs\OrderPaymentJob;
use App\Jobs\OrderStatusChangeJob;
use App\Models\Order;
use App\Models\Payment;
use App\Models\UserAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use User;

use function App\Helpers\createTransaction;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer',
            'payment_type' => 'required|in:credit,debit',
            'payment_method' => 'nullable|string',
            'note' => 'nullable|string',
            'invoice_id' => 'nullable|string'
        ]);
        $request['payment_date'] = now();
        $request['created_by'] = Auth::id();
        DB::beginTransaction();
        info('payable_type', [$request->input('payable_type')]);
        try {
            // For Supplier 
            if ((int) $request->input('payable_type') == config('app.transaction_payable_type.supplier')) {
                $customeNote = ($request->input('amount') . ' Tk paid for product purchased.' . ($request->input('invoice_id') ? 'Invoice id ' . $request->input('invoice_id') : '') . "--" . $request->note);
                $userPayType = 'add';
                $transactionType = 'purchase';
            }
            // For Wholesaler 
            if ((int) $request->input('payable_type') == config('app.transaction_payable_type.wholesaler')) {
                $customeNote = ($request->input('amount') . ' Tk paid for product sell.' . ($request->input('invoice_id') ? 'Invoice id ' . $request->input('invoice_id') : '') . "--" . $request->note);
                $userPayType = 'add';
                $transactionType = 'sell';
            }
            info('userPayType', [$userPayType]);
            info('transactionType', [$transactionType]);
            if ($request->input('amount') > 0) {
                $payment = handlePayment(
                    $request->input('payable_id'),
                    $request->input('payable_type'),
                    $request->input('amount'),
                    $transactionType, // Transaction type
                    $request->input('payment_type'),    // Payment type
                    $request->input('payment_method'), // Payment method
                    $request->input('invoice_id'),
                    $customeNote,
                    Auth::user()->id,
                    $request->input('date') ?? now()
                );
                info('payment', [$payment]);
                $userId = Auth::user()->id;
                handleUserAccountPayment($request->input('payable_id'), $request->input('amount'), $userPayType, $userId);

                if (!$payment) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Payment Insert error');
                }
            }
            // $payment = Payment::create($request->all());

            // $transaction = createTransaction($request->payable_id, $request->payable_type, $request->amount, 'purchase', Auth::id(), $request->note);

            // Payment::where('id', $payment->id)->update(['transaction_id' => $transaction->id]);

            // $userAccount = UserAccount::where('user_id', $request->payable_id)->first();
            // $userAccount->
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            info('Payment Error ', [$e]);
            return $e;
        }

        return redirect()->back()->with('success', 'Payments created successfully.');
        // return redirect()->route('suppliers.index')->with('success', 'Payments created successfully.');
    }
    public function  createImportOrderpaymentCsv()
    {
        $data = [];
        return view('payment_sheet.import', compact('data'));
    }

    public function importOrderpaymentCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        // Instantiate the import class
        $import = new OrderPaymentImport;

        // Import the data from the CSV file
        Excel::import($import, $request->file('file'));

        // Get the imported data
        $importedData = $import->getData();

        // Extract the order IDs from the imported data
        $orderIds = array_column(array_slice($importedData, 1), 0);

        // Fetch the orders from the database based on the order IDs
        $orders = Order::whereIn('id', $orderIds)->get()->keyBy('id');

        // Merge imported data with orders
        $mergedData = array_map(function ($row) use ($orders) {
            logger('Row ', [$row]);
            $orderId = $row[0];
            if (isset($orders[$orderId])) {
                // Add additional data from the order if needed
                $order = $orders[$orderId];
                $row['order'] = $order; // You can adjust this part as needed
            }
            return $row;
        }, array_slice($importedData, 1)); // Skip header row
        $data = $mergedData;
        return view('payment_sheet.import', compact('data'));

        // Output or return the merged data
        return response()->json($mergedData);
    }
    public function StoreOrderpaymentCsv(Request $request)
    {


        $request->validate([
            'payment_process_type' => 'required|in:1,2',
            'payment_data' => 'required',
        ]);
        $paymentData = json_decode($request->input('payment_data'), true);
        $unadjustableOrdersArray = json_decode($request->unadjustable_orders, true);
        $unadjustable_order_message = "Un adjustable Order ID:  " . implode(',', $unadjustableOrdersArray) . " Please Process it manuaaly";
        $userId = Auth::user()->id;

        foreach ($paymentData as $row) {
            // Assuming $row[0] is Order ID and $row[1] is Payment Amount
            $orderId = $row[0];
            $paymentAmount = $row[1];
            $order = $row['order'] ?? null;
            info('Order is -->', [$order['due']]);
            if ($request->input('payment_process_type') == 1) {
                if ($paymentAmount == $order['due']) {
                    OrderPaymentJob::dispatch($orderId, $paymentAmount, $userId);
                    if ($order['status_id'] == config('status.shipped')) {
                        OrderStatusChangeJob::dispatch($orderId, config('status.delivered'), Auth::id());
                    }
                }
            }
            if ($request->input('payment_process_type') == 2) {
                OrderPaymentJob::dispatch($orderId, $paymentAmount, $userId);
                if ($order['status_id'] == config('status.shipped')) {
                    OrderStatusChangeJob::dispatch($orderId, config('status.delivered'), Auth::id());
                }
            }
        }

        if ($request->input('payment_process_type') == 1) {
            return response()->json(['status' => 200, 'message' => 'Adjustable Orders Payments processed successfully.', 'orders' => $unadjustable_order_message]);
        }
        // Return response or redirect
        return response()->json(['status' => 200, 'message' => 'All Order Payments processed successfully.', 'orders' => 'All Ok']);
    }
}
