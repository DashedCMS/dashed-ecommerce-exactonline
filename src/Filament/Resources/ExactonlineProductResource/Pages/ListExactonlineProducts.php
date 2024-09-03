<?php

namespace Dashed\DashedEcommerceExactonline\Filament\Resources\ExactonlineProductResource\Pages;

use Dashed\DashedEcommerceCore\Models\Product;
use Filament\Resources\Pages\ListRecords;
use Dashed\DashedEcommerceExactonline\Filament\Resources\ExactonlineProductResource;
use Illuminate\Database\Eloquent\Builder;

class ListExactonlineProducts extends ListRecords
{
    protected static string $resource = ExactonlineProductResource::class;

    protected function getTableQuery(): ?Builder
    {
        $productIds = Product::isNotBundle()->notParentProduct()->pluck('id');

        return parent::getTableQuery()->whereIn('product_id', $productIds);
    }
}
