<?php

use Context\Cli\Console;

return function (Console $console) {

    $console->register('qapla', \Elements\Shipping\Commands\QaplaCommand::class . ':send');

    $console->register('sync', \Elements\Configurator\Commands\SyncCommand::class . ':sync');

    $console->register('newsletter', \Elements\Newsletter\Commands\SyncCommand::class . ':sync');

    $console->register('sendpulse', \Elements\Newsletter\Commands\SyncCommand::class . ':upload');

    $console->register('labels_clear', \Elements\Labels\Commands\LabelsCommand::class . ':clear');

    $console->register('orders_confirm', \Elements\Orders\Commands\OrdersCommand::class . ':notify');

    $console->register('token_clear', \Elements\Users\Commands\TokenCommand::class . ':clear');

};

?>