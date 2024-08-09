<?php

declare(strict_types=1);

namespace Elements\Import\Helpers;

use Kodelines\Db;



class Log
{

    public static function error(string $element, mixed $ref, string $error, $query = null) {
        Db::insert('import_errors',['element' => $element, 'ref' => $ref, 'error' => $error, 'query' => $query]);
    }
    

}