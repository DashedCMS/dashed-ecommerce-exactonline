<?php

namespace Qubiqx\QcommerceEcommerceExactonline;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Qubiqx\QcommerceEcommerceCore\Events\Orders\InvoiceCreatedEvent;
use Qubiqx\QcommerceEcommerceExactonline\Listeners\MarkOrderAsPushableListener;

class QcommerceEcommerceExactonlineEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InvoiceCreatedEvent::class => [
            MarkOrderAsPushableListener::class,
        ],
    ];
}
