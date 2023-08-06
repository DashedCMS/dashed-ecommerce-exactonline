<?php

namespace Dashed\DashedEcommerceExactonline;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Dashed\DashedEcommerceCore\Events\Orders\InvoiceCreatedEvent;
use Dashed\DashedEcommerceExactonline\Listeners\MarkOrderAsPushableListener;

class DashedEcommerceExactonlineEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InvoiceCreatedEvent::class => [
            MarkOrderAsPushableListener::class,
        ],
    ];
}
