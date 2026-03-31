<?php

namespace Dashed\DashedEcommerceExactonline;

use Livewire\Livewire;
use Illuminate\Support\Facades\Gate;
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
            $schedule->command(RefreshExactonlineTokenCommand::class)
                ->everyMinute()
                ->withoutOverlapping();
            $schedule->command(PushProductsToExactonlineCommand::class)
                ->everyFifteenMinutes()
                ->withoutOverlapping();
            //Only for vat rate atm, but not used
            //                    $schedule->command(SyncProductsWithExactonlineCommand::class)->everyFifteenMinutes();
            $schedule->command(PushOrdersToExactonlineCommand::class)
                ->everyMinute()
                ->withoutOverlapping();
        });

        Livewire::component('show-exactonline-order', ShowExactonlineOrder::class);

        Order::addDynamicRelation('exactonlineOrder', function (Order $model) {
            return $model->hasOne(ExactonlineOrder::class);
        });

        Product::addDynamicRelation('exactonlineProduct', function (Product $model) {
            return $model->hasOne(ExactonlineProduct::class);
        });
        Gate::policy(\Dashed\DashedEcommerceExactonline\Models\ExactonlineProduct::class, \Dashed\DashedEcommerceExactonline\Policies\ExactonlineProductPolicy::class);

        cms()->registerRolePermissions('Integraties', [
            'view_exactonline_product' => 'Exactonline producten bekijken',
            'edit_exactonline_product' => 'Exactonline producten bewerken',
            'delete_exactonline_product' => 'Exactonline producten verwijderen',
        ]);
    }

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        cms()->registerSettingsPage(ExactonlineSettingsPage::class, 'Exactonline', 'archive-box', 'Koppel Exactonline');

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

        cms()->builder('plugins', [
            new DashedEcommerceExactonlinePlugin(),
        ]);
    }
}
