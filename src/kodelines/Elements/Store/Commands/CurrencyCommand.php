<?php


declare(strict_types=1);

namespace Elements\Configurator\Commands;

use Kodelines\Abstract\Command;
use Kodelines\Helpers\Currencies;

class CurrencyCommand extends Command
{

    public function start(array $args) 
    {
        Currencies::sync();
    }


}