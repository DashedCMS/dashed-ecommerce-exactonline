<?php

namespace Dashed\DashedEcommerceExactonline;

use Dashed\DashedEcommerceCore\Events\Orders\InvoiceCreatedEvent;
use Dashed\DashedEcommerceExactonline\Listeners\MarkOrderAsPushableListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class DashedEcommerceExactonlineEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InvoiceCreatedEvent::class => [
            MarkOrderAsPushableListener::class,
        ],
    ];
}
