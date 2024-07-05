<?php

namespace Context\Cli;

use Throwable;
use Kodelines\Helpers\Language;

final class Factory
{

    public Console $console;



    public function createInstance()
    {

        Language::build();

        $this->console = new Console;

        try {

            // Register system routes
            (require __DIR__ . '/config/commands.php')($this->console);

            //Custom Routes for context
            if(file_exists(_DIR_CONTEXT_ . 'commands.php')) {
                (require _DIR_CONTEXT_ . '/commands.php')($this->console);
            }

            return $this->console->start();

        } catch ( Throwable $e){
            $this->console->error($e->getMessage(),true);
        }




    }



}