<?php

namespace Dashed\DashedEcommerceExactonline\Filament\Resources\ExactonlineProductResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Dashed\DashedEcommerceCore\Models\Product;
use Dashed\DashedEcommerceExactonline\Filament\Resources\ExactonlineProductResource;

class ListExactonlineProducts extends ListRecords
{
    protected static string $resource = ExactonlineProductResource::class;

    protected function getTableQuery(): ?Builder
    {
        $productIds = Product::isNotBundle()->notParentProduct()->pluck('id');

        return parent::getTableQuery()->whereIn('product_id', $productIds);
    }
}
