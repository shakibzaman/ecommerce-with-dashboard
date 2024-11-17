<?php

namespace App\Jobs;

use App\Models\CourierSheetOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CourierSheetOrderMakeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $courier_sheet_id;
    public $order_id;
    public function __construct($courier_sheet_id, $order_id)
    {
        $this->courier_sheet_id = $courier_sheet_id;
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger(' Order is ==>', [$this->order_id]);
        $courier_sheet_order = new CourierSheetOrder();
        $courier_sheet_order->courier_sheet_id = $this->courier_sheet_id;
        $courier_sheet_order->order_id = $this->order_id;
        $courier_sheet_order_mapping = $courier_sheet_order->save();
        if ($courier_sheet_order_mapping) {
            logger('Courier Sheet making done', ['order_id' => $this->order_id]);
        } else {
            logger('Failed to make Courier Sheet', ['order_id' => $this->order_id]);
        }
    }
}
