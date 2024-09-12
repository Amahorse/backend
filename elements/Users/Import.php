<?php

declare(strict_types=1);

namespace Elements\Users;

use Elements\Import\Helpers\Log;
use Kodelines\Tools\Json;
use Kodelines\Db;
use Kodelines\Helpers\Password;


class Import
{

    public static function start() {

        
        Db::getInstance()->skipError = true;

        //Foreach json categorie per inserimento o aggiornamento
        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/users.json') as $values) {

            $insert = $values;

            $insert['username'] = Users::generateUsername();

            $insert['id_countries'] = 380;

            $insert['language'] = 'it';

            $insert = array_merge($insert,Password::create());

            if(!Db::replace('users',$insert)) {

                Log::error('users',$insert['email'],'Errore inserimento utente',Db::getInstance()->lastError);

                continue;

            }



        }


        

    }
    

}