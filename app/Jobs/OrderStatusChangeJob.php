<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use function App\Helpers\handleStatus;

class OrderStatusChangeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $order_id;
    public $status_id;
    public $user_id;

    public function __construct($order_id, $status_id, $user_id)
    {
        $this->order_id = $order_id;
        $this->status_id = $status_id;
        $this->user_id = $user_id;
        logger('Job Call form here ' . $this->order_id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $statusUpdate = handleStatus($this->order_id, $this->status_id, $this->user_id);
        info('statusUpdate Jobs', [$statusUpdate]);
    }
}
