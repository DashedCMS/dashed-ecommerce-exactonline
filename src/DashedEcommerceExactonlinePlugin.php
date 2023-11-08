<?php

namespace Dashed\DashedEcommerceExactonline;

use Dashed\DashedEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;
use Dashed\DashedEcommerceExactonline\Filament\Widgets\ExactonlineOrderStats;
use Filament\Contracts\Plugin;
use Filament\Panel;

class DashedEcommerceExactonlinePlugin implements Plugin
{
    public function getId(): string
    {
        return 'dashed-ecommerce-exactonline';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->widgets([
                ExactonlineOrderStats::class,
            ])
            ->pages([
                ExactonlineSettingsPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {

    }
}
