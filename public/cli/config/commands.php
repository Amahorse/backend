<?php

use Context\Cli\Console;

return function (Console $console) {

    $console->register('mailer_queue', \Kodelines\Commands\MailerCommand::class . ':queue');

    $console->register('mailer_clear', \Kodelines\Commands\MailerCommand::class . ':clear');

    $console->register('cache_clear', \Kodelines\Commands\FolderCommand::class . ':cache');

    $console->register('temp_clear', \Kodelines\Commands\FolderCommand::class . ':temp');

};

?>