<?php

use App\Models\Supplier;
use App\Models\PurchasedProductFromSupplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    /**
     * Settle the dues for a supplier, including previous dues and current dues.
     *
     * @param int $supplierId
     * @param float $paymentAmount
     * @return bool
     */
    public function settleSupplierDues($supplierId, $paymentAmount)
    {
        // Begin a database transaction
        DB::beginTransaction();

        try {
            $supplier = User::findOrFail($supplierId);

            // Calculate total due amount
            $totalDue = $supplier->total_due_amount;

            if ($paymentAmount >= $totalDue) {
                // Full payment received
                $remainingAmount = $paymentAmount - $totalDue;

                // Reset previous due amount to zero
                $supplier->previous_due_amount = 0.00;

                // Reset all current dues in purchased products
                PurchasedProductFromSupplier::where('sup_id', $supplierId)
                    ->update(['due' => 0.00]);

                // Save the supplier with updated previous due amount
                $supplier->save();

                // Optionally, handle any excess payment (remainingAmount)
                // Example: Store as credit or return it to the supplier
            } else {
                // Handle partial payment logic

                // Deduct from the current dues first
                $remainingPayment = $paymentAmount;

                // Update current dues
                $this->settleCurrentDues($supplier, $remainingPayment);

                // If payment remains after settling current dues, apply to previous due amount
                if ($remainingPayment > 0) {
                    $supplier->previous_due_amount -= $remainingPayment;
                    $supplier->save();
                }
            }

            // Commit the transaction
            DB::commit();

            return true;
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollback();

            // Log the exception or handle it as needed
            // Log::error($e->getMessage());

            return false;
        }
    }

    /**
     * Settle the current dues for the supplier.
     *
     * @param Supplier $supplier
     * @param float &$remainingPayment
     * @return void
     */
    protected function settleCurrentDues(Supplier $supplier, &$remainingPayment)
    {
        // Get all unpaid purchases for the supplier ordered by date (oldest first)
        $purchases = PurchasedProductFromSupplier::where('sup_id', $supplier->id)
            ->where('due', '>', 0)
            ->orderBy('date', 'asc')
            ->get();

        foreach ($purchases as $purchase) {
            if ($remainingPayment <= 0) {
                break;
            }

            if ($remainingPayment >= $purchase->due) {
                // Full payment for this purchase
                $remainingPayment -= $purchase->due;
                $purchase->due = 0.00;
            } else {
                // Partial payment for this purchase
                $purchase->due -= $remainingPayment;
                $remainingPayment = 0.00;
            }

            // Save the updated purchase
            $purchase->save();
        }
    }
}
