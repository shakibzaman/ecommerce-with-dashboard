<?php

namespace App\Helpers;

use App\Models\Transaction;
use Exception;

// function createTransaction($user_id,)
// {

//     return "Transaction Create Done ";
// }

function createTransaction(int $payableId, string $payableType, string $amount, string $transactionType, string $createdBy, ?string $note = null): Transaction
{
    info('payableId', [$payableId]);
    info('payableType', [$payableType]);
    info('amount', [$amount]);
    info('transactionType', [$transactionType]);
    info('createdBy', [$createdBy]);
    info('note', [$note]);
    return Transaction::create([
        'payable_id' => $payableId,
        'payable_type' => $payableType,
        'amount' => (int) $amount,
        'transaction_type' => $transactionType,
        'created_by' => (int) $createdBy,
        'note' => $note,
    ]);
}
