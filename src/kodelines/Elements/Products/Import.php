<?php

declare(strict_types=1);

namespace Elements\Products;

use Kodelines\Tools\Json;
use Kodelines\Db;
use Elements\Import\Helpers\Log;
use Elements\Brands\Brands;
use Elements\Categories\Categories;
use Kodelines\Tools\Str;

class Import
{

    /**
     * Conversione campo codice famiglia
     */
    public static $famiglia = [
        'MWES' => 'western_riding', 
        'MING' => 'english_riding',
        'AMEF' => 'others_riding',
        'MIMW' => 'western_riding,english_riding',
        'PET' => 'pet',
        'SCDA' => 'stable'
    ];

    /*
     * Conversione campo codice sottofamiglia
     */
    public static $sottofamiglia = [
        'CAVA' => ['type' => 'horse'],
        'CAVK' => ['type' => 'rider', 'age' => 'child', 'gender' => 'male,female'],	
        'CAVM' => ['type' => 'rider', 'age' => 'adult', 'gender' => 'male'],
        'CAVU' => ['type' => 'rider', 'age' => 'adult', 'gender' => 'male,female'],
        'CAVW' => ['type' => 'rider', 'age' => 'adult', 'gender' => 'female'],
        'KIDM' => ['type' => 'rider', 'age' => 'child', 'gender' => 'male'],
        'KIDW' => ['type' => 'rider', 'age' => 'child', 'gender' => 'female'],
    ];


    /*
        TODO: creare categoria principale per questi elementi e associare sottocategorie

        AMON	Altre Monte	Other Ridings
        CURA	Cura del Cavallo	Horse Care
        DOG	Cane	Dog
        ELET	Attrezzatura Elettrica	Electrical Equipment
        FINI	Finimenti	Harness
        SCUD	Attrezzature da Scuderia	Stable Equipment
    */


    public static function start() {


        Db::getInstance()->skipError = true;

        $start = _NOW_;

        $products = Products::getCodes();

        $brands = Brands::getCodes();

        $categories = Categories::getCodes();

        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/products.json') as $values) {

            $insert = [];

            //Codice prodotto neutro è uguale a gamma
            $code = $values['code'];  

            $insert['code'] = $values['code'];

            //TODO: mettere controllo family e subfamily

            //Famiglia 
            $insert['family'] = self::$famiglia[$values['code_family']];

            //Conversione sottofamiglia
            if(isset($sottofamiglia[$values['code_subfamily']])) {
                $insert = array_merge(self::$sottofamiglia[$values['code_subfamily']],$insert);
            } 

            $insert['date_last_sync'] = _NOW_;

            //Variante split
            if(!empty($values['split'])) {
                $insert['split'] = mb_strtolower($values['split']);
            } else {
                $insert['split'] = NULL;
            }


            //Availability type è = 
            $insert['availability_type'] = $values['availability_type'];

            //Discount class
            if(!empty($values['discount_class'])) {
                $insert['discount_class'] = $values['discount_class'];
            } else {
                $insert['discount_class'] = NULL;
            }

            //Conversione disponibilità
            if(!empty($values['available_b2b']) && $values['available_b2b'] == 'SI') {
                $insert['available_b2b'] = 1;
            } else {
                $insert['available_b2b'] = 0;
            }

            if(!empty($values['available_b2c']) && $values['available_b2c'] == 'SI') {
                $insert['available_b2c'] = 1;
            } else {
                $insert['available_b2c'] = 0;
            }

            //Recupero opzioni varianti
            $a_options = options('products','a0');

            //Tipologie varianti a0
            if(!empty($values['tipologia_A0'])) {

                $a0 = Str::plain($values['tipologia_A0']);

                if(array_key_exists($a0,$a_options)) {
                    $insert['a0'] = $a0;
                } else {
                    Log::error('products',$code,'Tipologia A0 inesistente: ' . $a0);
                }

            } else {
                $insert['a0'] = NULL;
            }

            //Tipologie varianti a1
            if(!empty($values['tipologia_A1'])) {
                              
                $a1 = Str::plain($values['tipologia_A1']);

                if(array_key_exists($a1,$a_options)) {
                    $insert['a1'] = $a1;
                } else {
                    Log::error('products',$code,'Tipologia A1 inesistente: ' . $a1);
                }


            } else {
                $insert['a1'] = NULL;
            }

            //Tipologie varianti a1
            if(!empty($values['tipologia_A4'])) {

                $a4 = Str::plain($values['tipologia_A4']);

                if(array_key_exists($a4,$a_options)) {
                    $insert['a4'] = $a4;
                } else {
                    Log::error('products',$code,'Tipologia A1 inesistente: ' . $a4);
                }

            } else {
                $insert['a4'] = NULL;
            }


            //Recupero brand
            if(isset($brands[$values['brand_code']])) {
                
                $insert['id_brands'] = $brands[$values['brand_code']];

            } else {

                Log::error('products',$code,'Brand inesistente:' . $brands[$values['brand_code']]);
            
            }

            if(!isset($products[$code])) {

                if(!$insert['id'] = Db::insert('products',$insert)) {

                    Log::error('products',$code,'Errore inserimento prodotto',Db::getInstance()->lastError);

                    continue;
                }

            } else {

                $insert['id'] = $products[$code];

                if(!Db::updateArray('products',$insert,'code',$code)) {

                    Log::error('products',$code,'Errore aggiornamento prodotto',Db::getInstance()->lastError);

                }

            }

            //Lingua 
            $it = [
                'id_products' => $insert['id'],
                'title' => $values['title_ita'],
                'meta_title' => $values['title_ita'],
                'language' => 'it',
                'slug' => Str::plain($values['title_ita'])
            ];


            if(!Db::replace('products_lang',$it)) {

                Log::error('products_lang',$insert['code'],'Errore inserimento prodotto lingua IT',Db::getInstance()->lastError);

            }

            $en = [
                'id_products' => $insert['id'],
                'title' => $values['title_eng'],
                'meta_title' => $values['title_eng'],
                'language' => 'en',
                'slug' => Str::plain($values['title_eng'])
            ];

            if(!Db::replace('products_lang',$en)) {

                Log::error('products_lang',$insert['code'], 'Errore inserimento prodotto lingua EN',Db::getInstance()->lastError);

            }
        
            if(!empty($values['id_group']) && !empty($values['id_subgroup'])) {

                $category_code = $values['id_group'].'-'.$values['id_subgroup'];

                if(!isset($categories[$category_code])) {
                    
                    Log::error('products_categories','ID cat:' . $category_code .' codice prodotto:' .$code ,'Categoria inesistente');
                
                } else {
                    
                    if(!Db::replace('products_categories',['id_products' => $insert['id'], 'id_categories' => $categories[$category_code], 'main' => 1])) {
                        Log::error('products_categories','ID categoria:' . $categories[$category_code] .' codice prodotto:' . $code  ,'Categoria o prodotto inesistente',Db::getInstance()->lastError);
                    }

                }

            }

            //TODO: aggiungere sottocategorie quinsto, sesto, settimo livello 

        }
        
        Db::insert('import_logs',['import' => 'products','date_start' => $start,'date_end' => date("Y-m-d H:i:s")]);
    }




}