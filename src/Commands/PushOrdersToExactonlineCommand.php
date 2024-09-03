<?php

namespace Dashed\DashedEcommerceExactonline\Commands;

use Illuminate\Console\Command;
use Dashed\DashedEcommerceExactonline\Classes\Exactonline;
use Dashed\DashedEcommerceExactonline\Models\ExactonlineOrder;

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
            foreach (ExactonlineOrder::where('pushed', '!=', 1)->limit(50)->with(['order'])->get() as $exactonlineOrder) {
                $this->info('Pushing order ' . $exactonlineOrder->order->id);
                $response = Exactonline::pushOrder($exactonlineOrder->order);
                if ($response['success']) {
                    $this->info('Order pushed successfully');
                } else {
                    $this->error('Order push failed: ' . $response['error']);
                }
            }
        } else {
            $this->error('Exactonline is not connected, please connect first');
        }
    }
}
