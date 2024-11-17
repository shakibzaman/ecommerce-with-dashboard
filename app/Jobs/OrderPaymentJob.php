<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

use function App\Helpers\handleDueOrderPayment;

class OrderPaymentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $orderId;
    public $amount;
    public $userId;
    public function __construct($orderId, $amount, $userId)
    {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $due_order_payment = handleDueOrderPayment($this->orderId, $this->amount, $this->userId);
    }
}
