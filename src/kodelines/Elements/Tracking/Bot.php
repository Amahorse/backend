<?php


declare(strict_types=1);

namespace Elements\Tracking;

use Kodelines\Db;

class Bot  {


    public static function detected(string $user_agent):bool {

        return Db::replace('tracking_bots',['user_agent' => substr($user_agent,0,250),'date_update' => _NOW_]);
    }   


}

?>