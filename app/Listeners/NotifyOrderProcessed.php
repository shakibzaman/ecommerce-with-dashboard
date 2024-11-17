<?php

namespace App\Listeners;

use App\Events\OrderProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyOrderProcessed implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderProcessed $event): void
    {
        info("Listner => All Order Processed for Courier sheet id " . $event->courierSheetId);
    }
}
