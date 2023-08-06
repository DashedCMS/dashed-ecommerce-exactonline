<?php

namespace Dashed\DashedEcommerceExactonline\Filament\Widgets;

use Dashed\DashedEcommerceExactonline\Models\ExactonlineOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class ExactonlineOrderStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Aantal bestellingen naar Exactonline', ExactonlineOrder::where('pushed', 1)->count()),
            Card::make('Aantal bestellingen in de wacht', ExactonlineOrder::where('pushed', 0)->count()),
            Card::make('Aantal bestellingen gefaald', ExactonlineOrder::where('pushed', 2)->count()),
        ];
    }
}
