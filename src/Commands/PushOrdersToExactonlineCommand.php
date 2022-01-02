<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Commands;

use Illuminate\Console\Command;
use Qubiqx\QcommerceEcommerceExactonline\Classes\Exactonline;
use Qubiqx\QcommerceEcommerceExactonline\Models\ExactonlineOrder;

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
        if (Exactonline::connected()) {
            foreach(ExactonlineOrder::where('pushed', '!=', 1)->limit(5)->with(['order'])->get() as $exactonlineOrder){
                $this->info('order ' . $exactonlineOrder->order->id);
                Exactonline::pushOrder($exactonlineOrder->order);
            }
        }
    }
}
