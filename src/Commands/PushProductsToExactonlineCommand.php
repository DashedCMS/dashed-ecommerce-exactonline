<?php

namespace Dashed\DashedEcommerceExactonline\Commands;

use Illuminate\Console\Command;
use Dashed\DashedEcommerceCore\Models\Product;
use Dashed\DashedEcommerceExactonline\Classes\Exactonline;

class PushProductsToExactonlineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exactonline:push-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push products to Exact online';

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
        if (Exactonline::isConnected()) {
            foreach (Product::publicShowable()->isNotBundle()->get() as $product) {
                Exactonline::pushProduct($product);
            }
        }
    }
}
