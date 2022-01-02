<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Listeners;

use Qubiqx\QcommerceEcommerceCore\Events\Orders\InvoiceCreatedEvent;
use Qubiqx\QcommerceEcommerceExactonline\Classes\Exactonline;

class MarkOrderAsPushableListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(InvoiceCreatedEvent $event)
    {
        if (Exactonline::isConnected($event->order->site_id) && !$event->order->exactonlineOrder) {
            $event->order->exactonlineOrder()->create([]);
        }
    }
}
