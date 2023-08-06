<?php

namespace Dashed\DashedEcommerceExactonline\Listeners;

use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedEcommerceCore\Events\Orders\InvoiceCreatedEvent;

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
