<?php


declare(strict_types=1);

namespace Kodelines\Commands;

use Kodelines\Abstract\Command;
use Kodelines\Tools\Folder;

class FolderCommand extends Command
{

    /**
     * Pulisce cartella cache ma mantiene file index.php
     */
    public function cache() 
    {
        if(defined('_DIR_CACHE_')) {
            Folder::clear(_DIR_CACHE_,true);
        }
    }

    /**
     * Pulisce cartella temp ma mantiene file index.php
     */
    public function temp() 
    {
        if(defined('_DIR_TEMP_')) {
            Folder::clear(_DIR_TEMP_,true);
        }
    }


}