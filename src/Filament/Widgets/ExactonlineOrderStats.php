<?php

namespace Dashed\DashedEcommerceExactonline\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Dashed\DashedEcommerceExactonline\Models\ExactonlineOrder;

class ExactonlineOrderStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        return [
            StatsOverviewWidget\Stat::make('Aantal bestellingen naar Exactonline', ExactonlineOrder::where('pushed', 1)->count()),
            StatsOverviewWidget\Stat::make('Aantal bestellingen in de wacht', ExactonlineOrder::where('pushed', 0)->count()),
            StatsOverviewWidget\Stat::make('Aantal bestellingen gefaald', ExactonlineOrder::where('pushed', 2)->count()),
        ];
    }
}
