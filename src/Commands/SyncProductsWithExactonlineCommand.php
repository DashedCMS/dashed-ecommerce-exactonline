<?php

namespace Qubiqx\Qcommerce\Console\Commands\Exactonline;

use Illuminate\Console\Command;
use Qubiqx\QcommerceEcommerceCore\Models\Product;
use Qubiqx\QcommerceEcommerceExactonline\Classes\Exactonline;

class SyncProductsWithExactonlineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exactonline:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync products with Exact online';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Exactonline::connected()) {
            foreach (Product::publicShowable()->get() as $product) {
                Exactonline::syncProduct($product);
            }
        }
    }
}
