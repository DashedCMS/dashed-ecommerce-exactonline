<?php

namespace Dashed\DashedEcommerceExactonline\Commands;

use Dashed\DashedEcommerceExactonline\Classes\Exactonline;
use Dashed\DashedEcommerceExactonline\Models\ExactonlineOrder;
use Illuminate\Console\Command;

class PushOrdersToExactonlineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exactonline:push-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push orders to Exactonline';

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
     * @return int
     */
    public function handle()
    {
        if (Exactonline::isConnected()) {
            $this->info('Exactonline is connected, pushing orders');
            foreach (ExactonlineOrder::where('pushed', '!=', 1)->limit(5)->with(['order'])->get() as $exactonlineOrder) {
                $this->info('Pushing order ' . $exactonlineOrder->order->id);
                Exactonline::pushOrder($exactonlineOrder->order);
            }
        } else {
            $this->error('Exactonline is not connected, please connect first');
        }
    }
}
