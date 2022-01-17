<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Listeners;

use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceEcommerceCore\Events\Orders\InvoiceCreatedEvent;

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
        if (Customsetting::get('exactonline_client_id', $event->order->site_id) && ! $event->order->exactonlineOrder) {
            $event->order->exactonlineOrder()->create([]);
        }
    }
}
