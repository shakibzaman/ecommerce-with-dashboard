
<?php

use App\Models\Payment;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function App\Helpers\createTransaction;

if (!function_exists('handlePayment')) {
    function handlePayment($payable_id, $payable_type, $amount, $transaction_type, $payment_type, $payment_method, $invoice_id, $note, $created_by, $payment_date = null)
    {
        DB::beginTransaction();
        try {
            $createPayment = Payment::create(
                [
                    'payable_id' => $payable_id,
                    'payable_type' => $payable_type,
                    'amount' => $amount,
                    'payment_type' => $payment_type,
                    'payment_date' => $payment_date ?? now(),
                    'payment_method' => $payment_method,
                    'invoice_id' => $invoice_id,
                    'note' => $note,
                    'created_by' => $created_by,
                ]
            );

            if ($createPayment) {
                $transactionCreate = createTransaction(
                    $payable_id,
                    $payable_type,
                    $amount,
                    $transaction_type,
                    $created_by,
                    $note
                );

                $createPayment->transaction_id = $transactionCreate->id;
                $createPayment->save();
                DB::commit();
            } else {
                DB::rollBack();
                info('Error while Create Payment');
                return false;
            }
            DB::commit();

            return $createPayment;
        } catch (\Exception $e) {
            DB::rollBack();
            info('Handle Payment Exception ==> ' . $e);
            return false;
        }
    }

    function handleUserAccountPayment($user_id, $amount, $amount_type, $createdBy, $invoice_id = null)
    {
        logger('user id', [$user_id]);
        logger('Call - 1');
        info('amount_type handle Acocont payment', [$amount_type]);
        DB::beginTransaction();
        try {

            $userAccount = UserAccount::with('user')->where('user_id', $user_id)->first();
            info('user Account is ', [$userAccount]);
            // For Supplier
            if ($userAccount->user->type == config('app.user_type.supplier')) {
                $transactionType = 'purchase';
                $customeNote = 'Product Purchased Due, Invoice ID ' . ($invoice_id ? $invoice_id : '');
                $payable_type = config('app.transaction_payable_type.supplier');
            }
            // For Wholesaler 
            if ($userAccount->user->type == config('app.user_type.wholesaler')) {
                $transactionType = 'sell';
                $customeNote = 'Product Sell Due, Invoice ID ' . ($invoice_id ? $invoice_id : '');
                $payable_type = config('app.transaction_payable_type.wholesaler');
            }


            if (!$userAccount) {
                DB::rollBack();
                info('No user Account found during payment ');
                return redirect()->back()->with('error', 'No User Account found');
            }

            // Update the user's account balance
            $userAccount->total_due += $amount_type == 'due' ? $amount : -$amount;
            $userAccount->save();

            // Create a transaction if it's a due amount
            if ($amount_type == 'due') {
                createTransaction(
                    $user_id,
                    $payable_type,
                    $amount,
                    $transactionType,
                    $createdBy,
                    $customeNote
                );
            }
            // if ($amount_type == 'add') {
            //     createTransaction(
            //         $user_id,
            //         $payable_type,
            //         $amount,
            //         $transactionType,
            //         Auth::user()->id,
            //         $customeNote
            //     );
            // }
            logger('Call - 1 - End ');
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception in handleUserAccountPayment: ' . $e->getMessage());
            return false;
        }
    }
}
