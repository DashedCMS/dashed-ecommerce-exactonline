<?php

namespace Qubiqx\QcommerceEcommerceExactonline;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Qubiqx\QcommerceEcommerceExactonline\Commands\QcommerceEcommerceExactonlineCommand;

class QcommerceEcommerceExactonlineServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('qcommerce-ecommerce-exactonline')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_qcommerce-ecommerce-exactonline_table')
            ->hasCommand(QcommerceEcommerceExactonlineCommand::class);
    }
}
