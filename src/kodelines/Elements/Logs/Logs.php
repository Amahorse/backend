<?php

declare(strict_types=1);

namespace Elements\Logs;

use Kodelines\Tools\Folder;
use Kodelines\Tools\Str;

class Logs {


    public static function list():array {

        $logs = [];

        foreach(Folder::read(_DIR_LOGS_) as $value) {

            if(!Str::endsWith($value,'.log')) {
                continue;
            }

            $logs[] = ['name' => $value, 'file' => _DIR_LOGS_ . $value];
        }

        return $logs;
    }

    
    public static function get(string $log):bool|string {

        if(!file_exists(_DIR_LOGS_ . $log)) {
            return false;
        }

        return file_get_contents(_DIR_LOGS_ . $log);
        
    }
  
  

}