<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Commands;

use Illuminate\Console\Command;

class QcommerceEcommerceExactonlineCommand extends Command
{
    public $signature = 'qcommerce-ecommerce-exactonline';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
