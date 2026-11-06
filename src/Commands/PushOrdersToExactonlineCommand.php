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

    public const RETRY_FAILED_AFTER_MINUTES = 60;

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

            // Een mislukte bestelling (pushed = 2) maximaal eens per uur opnieuw.
            // Elke poging kan een API-aanroep en een beheerdersmail zijn, en met de
            // limiet hielden 50 vaste fouten elke nieuwe bestelling tegen. Nieuwe
            // eerst; de knop bij de bestelling zet pushed op 0 en wacht dus niet.
            $exactonlineOrders = ExactonlineOrder::query()
                ->where('pushed', '!=', 1)
                ->where(fn ($query) => $query->where('pushed', '!=', 2)->orWhere('updated_at', '<=', now()->subMinutes(self::RETRY_FAILED_AFTER_MINUTES)))
                ->whereHas('order')
                ->orderBy('pushed')
                ->orderBy('updated_at')
                ->limit(50)
                ->with(['order'])
                ->get();

            foreach ($exactonlineOrders as $exactonlineOrder) {
                $this->info('Pushing order ' . $exactonlineOrder->order->id);

                // pushOrder gooit als een product nog niet in Exact staat; dat mag
                // de rest van de ronde niet tegenhouden.
                try {
                    $response = Exactonline::pushOrder($exactonlineOrder->order);
                } catch (\Throwable $e) {
                    report($e);
                    $response = ['success' => false, 'error' => $e->getMessage()];
                }

                if ($response['success']) {
                    $this->info('Order pushed successfully');
                } else {
                    $this->error('Order push failed: ' . $response['error']);
                }

                // Een gelijke foutmelding maakt de rij niet dirty, dus de klok
                // moet hier expliciet verzet worden.
                ExactonlineOrder::whereKey($exactonlineOrder->id)->where('pushed', 2)->update(['updated_at' => now()]);
            }
        } else {
            $this->error('Exactonline is not connected, please connect first');
        }
    }
}
