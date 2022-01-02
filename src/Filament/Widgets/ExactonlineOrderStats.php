<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Qubiqx\QcommerceEcommerceExactonline\Models\ExactonlineOrder;

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
