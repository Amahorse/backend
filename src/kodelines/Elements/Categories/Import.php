<?php

declare(strict_types=1);

namespace Elements\Categories;

use Elements\Import\Helpers\Log;
use Kodelines\Tools\Json;
use Kodelines\Db;
use Kodelines\Tools\Str;


class Import
{

    public static function start() {

        
        Db::getInstance()->skipError = true;

        $insert['date_last_sync'] = _NOW_;

        //recupero categorie esistenti dal db per controllo insert, update o eliminazione
        $exists = Categories::getCodes();


        //Foreach json categorie per inserimento o aggiornamento
        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/categories.json') as $values) {

            $insert = [];

            $insert['uniqid'] = Categories::getUniqId($values);

            $insert['date_last_sync'] = _NOW_;

            //L'id si recuper dai vecchi se settato l'uniq id
            if(isset($exists[$insert['uniqid']])) {

                $insert['id'] = $exists[$insert['uniqid']];

                if(!Db::updateArray('categories',$insert,'id',$insert['id'])) {

                    Log::error('categories',$insert['uniqid'],'Errore aggiornamento categoria',Db::getInstance()->lastError);

                    continue;

                }

            } else {

                if(!$insert['id'] = Db::insert('categories',$insert)) {

                    $exists[$insert['uniqid']] = $insert['id'];

                    Log::error('categories',$insert['uniqid'],'Errore inserimento categoria',Db::getInstance()->lastError);

                    continue;

                }

            }

            //Lingua 
            $it = [
                'id_categories' => $insert['id'],
                'title' => $values['category_name_it'],
                'meta_title' => $values['category_name_it'],
                'language' => 'it',
                'slug' => Str::plain($values['category_name_it'])
            ];


            if(!Db::replace('categories_lang',$it)) {

                Log::error('categories_lang',$insert['uniqid'],'Errore inserimento categoria lingua IT',Db::getInstance()->lastError);

                continue;

            }

            $en = [
                'id_categories' => $insert['id'],
                'title' => $values['category_name_ing'],
                'meta_title' => $values['category_name_ing'],
                'language' => 'en',
                'slug' => Str::plain($values['category_name_ing'])
            ];

            if(!Db::replace('categories_lang',$en)) {

                Log::error('categories_lang',$insert['uniqid'],'Errore inserimento categoria lingua EN',Db::getInstance()->lastError);

                continue;

            }

        }
        
        
        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/categories.json') as $values) {

            $insert = [];

            $uniqid = Categories::getUniqId($values);

            //Ricavo id univoco categoria su cui sto lavorando in base a id univoco
            if(empty($values['id_parent_category']) || $values['id_parent_category'] == '0') {

                $id_categories_main = NULL;

            } else {

                if(!$id_categories_main = Db::getValue("SELECT id FROM categories WHERE uniqid = " . encode($values['id_parent_category']),true)) {
                
                    Log::error('categories',$uniqid,'Categoria principale non trovata',Db::getInstance()->lastError);
    
                    continue;
                }
    

            }


            if(!isset($exists[$uniqid])) {
                
                Log::error('categories',$insert['id_categories_main'],'Categoria principale non trovata',Db::getInstance()->lastError);

                continue;
            }

            $id_categories = $exists[$uniqid];


            if(!Db::update('categories','id_categories_main',$id_categories_main,'id',$id_categories)) {

                Log::error('categories',$uniqid,'Errore aggiornamento categoria',Db::getInstance()->lastError);

                continue;

            }


        }
        
        

    }
    

}