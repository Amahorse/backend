<?php


declare(strict_types=1);

namespace Elements\Orders\Commands;

use Kodelines\Abstract\Command;
use Elements\Orders\Orders;

class OrdersCommand extends Command
{


    public function notify() 
    {   
        Orders::notify();
    }



}