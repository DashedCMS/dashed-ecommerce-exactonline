<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Commands;

use Illuminate\Console\Command;
use Qubiqx\QcommerceEcommerceExactonline\Classes\Exactonline;

class RefreshExactonlineTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exactonline:refreshtoken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh exact online token';

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
        Exactonline::refreshToken();
    }
}
