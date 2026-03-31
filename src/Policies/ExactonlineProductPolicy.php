<?php

namespace Dashed\DashedEcommerceExactonline\Policies;

use Dashed\DashedCore\Policies\BaseResourcePolicy;

class ExactonlineProductPolicy extends BaseResourcePolicy
{
    protected function resourceName(): string
    {
        return 'ExactonlineProduct';
    }
}
