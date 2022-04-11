<?php

namespace Qubiqx\QcommerceEcommerceExactonline;

use Qubiqx\QcommerceEcommerceCore\Events\Orders\InvoiceCreatedEvent;
use Qubiqx\QcommerceEcommerceExactonline\Listeners\MarkOrderAsPushableListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class QcommerceEcommerceExactonlineEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InvoiceCreatedEvent::class => [
            MarkOrderAsPushableListener::class,
        ],
    ];
}
