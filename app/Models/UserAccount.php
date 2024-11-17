<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function App\Helpers\createTransaction;
use Illuminate\Support\Facades\Auth;

class UserAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'previous_due',
        'total_due',
    ];

    public function createTransaction(?string $transactionType = null, ?string $note = null)
    {
        // Log the data for debugging purposes
        info('previous_due', [$this->previous_due]);
        info('note', [$note]);
        info('User id', [Auth::id()]);

        // Assuming createTransaction is a valid function that processes the transaction
        $transaction = createTransaction(
            $this->user_id,          // User ID related to the transaction
            2,                       // Some static value (assuming transaction type/category)
            $this->previous_due,      // The previous due amount
            $transactionType,         // Dynamic transaction type
            Auth::id(),               // Creator ID (current logged-in user)
            $note                     // Dynamic note passed from the controller
        );

        // Log the created transaction for debugging
        info('transaction', [$transaction]);

        return $transaction; // Return the transaction object or ID if needed
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
