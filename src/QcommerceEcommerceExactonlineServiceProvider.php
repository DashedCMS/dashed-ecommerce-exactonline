<?php

namespace Qubiqx\QcommerceEcommerceExactonline;

<<<<<<< HEAD
use Filament\PluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Qubiqx\QcommerceEcommerceCore\Models\Order;
use Qubiqx\QcommerceEcommerceExactonline\Filament\Pages\Settings\ExactonlineSettingsPage;
=======
use Qubiqx\QcommerceEcommerceExactonline\Commands\QcommerceEcommerceExactonlineCommand;
>>>>>>> 803c877f8216589c70041ae82e1a5ba50aaa161a
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QcommerceEcommerceExactonlineServiceProvider extends PluginServiceProvider
{
    public static string $name = 'qcommerce-ecommerce-exactonline';

    public function bootingPackage()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
//            $schedule->command(PushOrdersToEboekhoudenCommand::class)->everyFifteenMinutes();
        });

//        Livewire::component('show-exactonline-order', ShowEboekhoudenShopOrder::class);

        Order::addDynamicRelation('exactonlineOrder', function (Order $model) {
//            return $model->hasOne(EboekhoudenOrder::class);
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

        ecommerce()->builder(
            'orderSideWidgets',
            array_merge(ecommerce()->builder('orderSideWidgets'), [
                'show-exactonline-order' => [
                    'name' => 'show-exactonline-order',
                ],
            ])
        );

        $package
            ->name('qcommerce-ecommerce-exactonline')
            ->hasViews()
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
//            EboekhoudenOrderStats::class,
        ]);
    }
}
