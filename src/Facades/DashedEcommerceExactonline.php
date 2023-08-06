<?php

namespace Dashed\DashedEcommerceExactonline\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dashed\DashedEcommerceExactonline\DashedEcommerceExactonline
 */
class DashedEcommerceExactonline extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dashed-ecommerce-exactonline';
    }
}
