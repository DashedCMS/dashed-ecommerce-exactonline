<?php

namespace Qubiqx\QcommerceEcommerceExactonline;

use Filament\PluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Livewire\Livewire;
use Qubiqx\QcommerceEcommerceCore\Models\Order;
use Qubiqx\QcommerceEcommerceCore\Models\Product;
use Qubiqx\QcommerceEcommerceExactonline\Commands\PushOrdersToExactonlineCommand;
use Qubiqx\QcommerceEcommerceExactonline\Commands\PushProductsToExactonlineCommand;
use Qubiqx\QcommerceEcommerceExactonline\Commands\RefreshExactonlineTokenCommand;
use Qubiqx\QcommerceEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;
use Qubiqx\QcommerceEcommerceExactonline\Filament\Widgets\ExactonlineOrderStats;
use Qubiqx\QcommerceEcommerceExactonline\Livewire\Orders\ShowExactonlineOrder;
use Qubiqx\QcommerceEcommerceExactonline\Models\ExactonlineOrder;
use Qubiqx\QcommerceEcommerceExactonline\Models\ExactonlineProduct;
use Spatie\LaravelPackageTools\Package;

class QcommerceEcommerceExactonlineServiceProvider extends PluginServiceProvider
{
    public static string $name = 'qcommerce-ecommerce-exactonline';

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
                    'icon' => 'archive',
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
            ->name('qcommerce-ecommerce-exactonline')
            ->hasViews()
            ->hasRoutes([
                'exactonlineRoutes',
            ])
            ->hasCommands([
//                PushOrdersToEboekhoudenCommand::class,
            ]);
    }

    protected function getPages(): array
    {
        return array_merge(parent::getPages(), [
            ExactonlineSettingsPage::class,
        ]);
    }

    protected function getWidgets(): array
    {
        return array_merge(parent::getWidgets(), [
            ExactonlineOrderStats::class,
        ]);
    }
}
