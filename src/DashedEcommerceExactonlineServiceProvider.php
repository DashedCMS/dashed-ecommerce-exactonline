<?php

namespace Dashed\DashedEcommerceExactonline;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Illuminate\Console\Scheduling\Schedule;
use Dashed\DashedEcommerceCore\Models\Order;
use Dashed\DashedEcommerceCore\Models\Product;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Dashed\DashedEcommerceExactonline\Models\ExactonlineOrder;
use Dashed\DashedEcommerceExactonline\Models\ExactonlineProduct;
use Dashed\DashedEcommerceExactonline\Livewire\Orders\ShowExactonlineOrder;
use Dashed\DashedEcommerceExactonline\Commands\PushOrdersToExactonlineCommand;
use Dashed\DashedEcommerceExactonline\Commands\RefreshExactonlineTokenCommand;
use Dashed\DashedEcommerceExactonline\Commands\PushProductsToExactonlineCommand;
use Dashed\DashedEcommerceExactonline\Commands\SyncProductsWithExactonlineCommand;
use Dashed\DashedEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;

class DashedEcommerceExactonlineServiceProvider extends PackageServiceProvider
{
    public static string $name = 'dashed-ecommerce-exactonline';

    public function bootingPackage()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command(RefreshExactonlineTokenCommand::class)->everyMinute();
            $schedule->command(PushProductsToExactonlineCommand::class)->everyFifteenMinutes();
            //Only for vat rate atm, but not used
            //                    $schedule->command(SyncProductsWithExactonlineCommand::class)->everyFifteenMinutes();
            $schedule->command(PushOrdersToExactonlineCommand::class)->everyMinute();
        });

        Livewire::component('show-exactonline-order', ShowExactonlineOrder::class);

        Order::addDynamicRelation('exactonlineOrder', function (Order $model) {
            return $model->hasOne(ExactonlineOrder::class);
        });

        Product::addDynamicRelation('exactonlineProduct', function (Product $model) {
            return $model->hasOne(ExactonlineProduct::class);
        });
    }

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        cms()->builder(
            'settingPages',
            array_merge(cms()->builder('settingPages'), [
                'exactonline' => [
                    'name' => 'Exactonline',
                    'description' => 'Koppel Exactonline',
                    'icon' => 'archive-box',
                    'page' => ExactonlineSettingsPage::class,
                ],
            ])
        );

        ecommerce()->widgets(
            'orders',
            array_merge(ecommerce()->widgets('orders'), [
                'show-exactonline-order' => [
                    'name' => 'show-exactonline-order',
                    'width' => 'sidebar',
                ],
            ])
        );

        $package
            ->name('dashed-ecommerce-exactonline')
            ->hasViews()
            ->hasRoutes([
                'exactonlineRoutes',
            ])
            ->hasCommands([
                RefreshExactonlineTokenCommand::class,
                PushProductsToExactonlineCommand::class,
                SyncProductsWithExactonlineCommand::class,
                PushOrdersToExactonlineCommand::class,
            ]);
    }
}
