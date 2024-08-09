<?php

declare(strict_types=1);

namespace Elements\Brands;

use Kodelines\Tools\Json;
use Kodelines\Db;
use Elements\Import\Helpers\Log;
use Kodelines\Tools\Str;



class Import
{

    public static function start() {

        Db::getInstance()->skipError = true;

        //recupero categorie esistenti dal db per controllo insert, update o eliminazione
        $exists = Brands::getCodes();

        //Foreach json categorie per inserimento o aggiornamento
        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/brand.json') as $values) {

            $insert = [];

            $insert['date_last_sync'] = _NOW_;

            $insert['code'] = $values['brand_code'];

            $insert['title'] = $values['brand_description'];

            $insert['date_last_sync'] = _NOW_;

            //L'id si recuper dai vecchi se settato l'uniq id
            if(isset($exists[$insert['code']])) {

                $insert['id'] = $exists[$insert['code']];

                if(!Db::updateArray('brands',$insert,'id',$insert['id'])) {

                    Log::error('brands',$insert['code'],'Errore aggiornamento brand',Db::getInstance()->lastError);

                    continue;

                }

            } else {

                if(!$insert['id'] = Db::insert('brands',$insert)) {

                    $exists[$insert['code']] = $insert['id'];

                    Log::error('brands',$insert['code'],'Errore inserimento brand',Db::getInstance()->lastError);

                    continue;

                }

            }

            //Lingua 
            $it = [
                'id_brands' => $insert['id'],
                'meta_title' => $values['brand_description'],
                'language' => 'it',
                'slug' => Str::plain($values['brand_description'])
            ];


            if(!Db::replace('brands_lang',$it)) {

                Log::error('brands_lang',$insert['code'],'Errore inserimento brand lingua IT',Db::getInstance()->lastError);

                continue;

            }

            $en = [
                'id_brands' => $insert['id'],
                'meta_title' => $values['brand_description'],
                'language' => 'en',
                'slug' => Str::plain($values['brand_description'])
            ];

            if(!Db::replace('brands_lang',$en)) {

                Log::error('brands_lang',$insert['code'], ' En','Errore inserimento brand lingua EN',Db::getInstance()->lastError);

                continue;

            }

        }
        

    }
    

}