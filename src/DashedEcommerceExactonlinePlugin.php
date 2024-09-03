<?php

namespace Dashed\DashedEcommerceExactonline;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Dashed\DashedEcommerceExactonline\Filament\Widgets\ExactonlineOrderStats;
use Dashed\DashedEcommerceExactonline\Filament\Resources\ExactonlineProductResource;
use Dashed\DashedEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;

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
            ])
            ->resources([
                ExactonlineProductResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {

    }
}
