<?php

declare(strict_types=1);

namespace Kodelines;

//https://github.com/peppeocchi/php-cron-scheduler
use \GO\Scheduler;
use InvalidArgumentException;


class Cron
{

  public function __construct()
  {
    
    // Create a new scheduler
    $scheduler = new Scheduler();

    Context::$config = new Config();

    define('_SCRIPT_RUNNER_', _DIR_PUBLIC_ . 'cli/run');

    // ... configure the scheduled jobs (see below) ...
    if(!empty($config->values['domains'])) {

        foreach(Context::$config->values['domains'] as $domain => $values) {
            
            if(empty($values['cron'])) {
                continue;
            }
            
            foreach($values['cron'] as $time => $command) {
   
                $execute =  _SCRIPT_RUNNER_ . ' ' . $domain . ' ' . $command;

                try {

                    //La modalità con file temporaneo fa un lock che impedisce allo script precedente di essere eseguito finchè l'altro non è finito
                    if(is_dir(_DIR_TEMP_) && is_writable(_DIR_TEMP_)) {
 
                        $scheduler->php($execute,'php')->at($time)->onlyOne(substr(_DIR_TEMP_,0,-1))->before(function () use ($execute) {
                           
                            new Log('cron', $execute, ['singlemode' => true, 'started' => time()]);

                        })->then(function ($output) use ($execute) {
                        
                            new Log('cron', $execute, ['singlemode' => true, 'output' => $output]);

                        }, true);

                    } else {

                        $scheduler->php($execute,'php')->at($time)->before(function () use ($execute) {
                           
                            new Log('cron', $execute, ['singlemode' => true, 'started' => time()]);

                        })->then(function ($output) use ($execute) {

                            new Log('cron', $execute, ['singlemode' => false, 'output' => $output]);

                        }, true);

                    }

                } catch (InvalidArgumentException $e) {

                    new Log('cron', $execute, ['error' => $e->getMessage()]);

                }
                
        
                
            }

        }

    }


    // Let the scheduler execute jobs which are due.
    $scheduler->run();


  }


}
