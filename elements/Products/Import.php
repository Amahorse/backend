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
        'SCDA' => 'stable',
        'ESPO' => 'display'
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

        $gamma = self::icons('gamma');

        $fields = self::icons('fields');

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


            //Variante split
            if(!empty($values['split'])) {
                $insert['split'] = mb_strtolower($values['split']);
            } else {
                $insert['split'] = NULL;
            }


            //Availability type è = 
            if(!empty($values['market_sale']) && $values['market_sale'] == 'ITA') {
                $insert['market_sale'] = 'it';
            } else {
                $insert['market_sale'] = NULL;
            }

            //Discount class
            if(!empty($values['discount_product_percentage'])) {
                $insert['discount_product_percentage'] = $values['discount_product_percentage'];
            } else {
                $insert['discount_product_percentage'] = NULL;
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

            //Icons
            if(!empty($values['tech_icon'])) {

                $icons = explode(';',$values['tech_icon']);

                foreach($icons as $icon) {

                    $icon_name = str_replace('.svg','',trim($icon));

                    if(empty($icon_name)) {
                        continue;
                    }
        
                    //Controllo se valore corrisponde a icona gamma
                    if(isset($gamma[$icon_name])) {

                        if(!file_exists(_DIR_UPLOADS_ .'icons/' . $gamma[$icon_name] . '.svg')) {
                            Log::error('products',$code,'File Icona inesistente: ' . $gamma[$icon_name] . '.svg');
                        }
       

                        if(isset($fields[$gamma[$icon_name]])) {

                            //TODO: fare ogni volta questo può rallentare, fare un array con i valori possibili
                            $options = options('products',$fields[$gamma[$icon_name]]);

                            if(!in_array($gamma[$icon_name],$options)) {
                                Log::error('products',$code,'Valore campo "' . $fields[$gamma[$icon_name]] . '" non valido: ' . $gamma[$icon_name]);
                            }

                            if(isset($insert[$fields[$gamma[$icon_name]]])) {
                                $insert[$fields[$gamma[$icon_name]]] .= ',' . $gamma[$icon_name];
                            } else {
                                $insert[$fields[$gamma[$icon_name]]] = $gamma[$icon_name];
                            }

                        } 

                    } else {
                       
                        Log::error('products',$code,'Icona non riconosciuta: ' . $icon_name);
                        
    
                    }

                }
            
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
                'slug' => Str::plain($values['title_ita']),
                'tech_spech' => !empty($values['tech_spec_ita']) ? str_replace('/n','',$values['tech_spec_ita']) : null,
                'description' => !empty($values['catchline_ita']) ? strip_tags(str_replace('/n','',$values['catchline_ita'])) : null,
                'content' => !empty($values['description_ita']) ? str_replace('/n','',$values['description_ita']) : null,
                'size_fit' => !empty($values['size_fit_ita']) ? str_replace('/n','',$values['size_fit_ita']) : null,
                'composition' => !empty($values['composition_ita']) ? str_replace('/n','',$values['composition_ita']) : null,
                'info_care' => !empty($values['info_care_ita']) ? str_replace('/n','',$values['info_care_ita']) : null,
                'meta_title' => !empty($values['tag_title_ita']) ? str_replace(' | Shop | Equestro','',$values['tag_title_ita']) : null,
                'meta_description' => !empty($values['meta_desc_ita']) ? str_replace('/n','',$values['meta_desc_ita']) : null,
            ];

            //TODO: su import logs fare start subito e end a null se non ha concluso

            if(!Db::replace('products_lang',$it)) {

                Log::error('products_lang',$insert['code'],'Errore inserimento prodotto lingua IT',Db::getInstance()->lastError);

            }

            $en = [
                'id_products' => $insert['id'],
                'title' => $values['title_eng'],
                'meta_title' => $values['title_eng'],
                'language' => 'en',
                'slug' => Str::plain($values['title_eng']),
                'tech_spech' => !empty($values['tech_spec_eng']) ? str_replace('/n','',$values['tech_spec_eng']) : null,
                'description' => !empty($values['catchline_eng']) ? str_replace('/n','',$values['catchline_eng']) : null,
                'content' => !empty($values['description_eng']) ? str_replace('/n','',$values['description_eng']) : null,
                'size_fit' => !empty($values['size_fit_eng']) ? str_replace('/n','',$values['size_fit_eng']) : null,
                'composition' => !empty($values['composition_eng']) ? str_replace('/n','',$values['composition_eng']) : null,
                'info_care' => !empty($values['info_care_eng']) ? str_replace('/n','',$values['info_care_eng']) : null,
                'meta_title' => !empty($values['tag_title_eng']) ? str_replace(' | Shop | Equestro','',$values['tag_title_eng']) : null,
                'meta_description' => !empty($values['meta_desc_eng']) ? str_replace('/n','',$values['meta_desc_eng']) : null,
            ];

            if(!Db::replace('products_lang',$en)) {

                Log::error('products_lang',$insert['code'], 'Errore inserimento prodotto lingua EN',Db::getInstance()->lastError);

            }

            //Importazione categorie 
            if(!empty($values['id_group'])) {

                $category_code = trim($values['id_group']);

                if(!isset($categories[$category_code])) {
                    
                    Log::error('products_categories','ID cat:' . $category_code .' codice prodotto:' .$code ,'Categoria inesistente');
                
                } else {
                    
                    if(!Db::replace('products_categories',['id_products' => $insert['id'], 'id_categories' => $categories[$category_code], 'main' => 1])) {
                        Log::error('products_categories','ID categoria:' . $categories[$category_code] .' codice prodotto:' . $code  ,'Categoria o prodotto inesistente',Db::getInstance()->lastError);
                    }

                }

            }
        
            if(!empty($values['id_group']) && !empty($values['id_subgroup'])) {

                $category_code = trim($values['id_group']).'-'.trim($values['id_subgroup']);

                if(!isset($categories[$category_code])) {
                    
                    Log::error('products_categories','ID cat:' . $category_code .' codice prodotto:' .$code ,'Categoria inesistente');
                
                } else {
                    
                    if(!Db::replace('products_categories',['id_products' => $insert['id'], 'id_categories' => $categories[$category_code], 'main' => 1])) {
                        Log::error('products_categories','ID categoria:' . $categories[$category_code] .' codice prodotto:' . $code  ,'Categoria o prodotto inesistente',Db::getInstance()->lastError);
                    }

                }

            }

            if($values['brand_code'] == 'AC' || $values['brand_code'] == 'ACA') {

                if(!empty($values['id_group']) && !empty($values['id_subgroup']) && !empty($values['id_lvl5'])) {

                    $category_code = trim($values['id_group']).'-'.trim($values['id_subgroup']) .'-'.trim($values['id_lvl5']);

                    if(!isset($categories[$category_code])) {
                        
                        Log::error('products_categories','ID categoria: ' . $category_code .' codice prodotto:' .$code ,'Categoria inesistente');
                    
                    } else {
                        
                        if(!Db::replace('products_categories',['id_products' => $insert['id'], 'id_categories' => $categories[$category_code], 'main' => 1])) {
                            Log::error('products_categories','ID categoria: ' . $categories[$category_code] .' codice prodotto:' . $code  ,'Categoria o prodotto inesistente',Db::getInstance()->lastError);
                        }

                    }

                }

                if(!empty($values['id_group']) && !empty($values['id_subgroup']) && !empty($values['id_lvl5']) && !empty($values['id_lvl6'])) {

                    $category_code = trim($values['id_group']).'-'.trim($values['id_subgroup']) .'-'.trim($values['id_lvl5']) .'-'.trim($values['id_lvl6']);

                    if(!isset($categories[$category_code])) {
                        
                        Log::error('products_categories','ID cat:' . $category_code .' codice prodotto:' .$code ,'Categoria inesistente');
                    
                    } else {
                        
                        if(!Db::replace('products_categories',['id_products' => $insert['id'], 'id_categories' => $categories[$category_code], 'main' => 1])) {
                            Log::error('products_categories','ID categoria:' . $categories[$category_code] .' codice prodotto:' . $code  ,'Categoria o prodotto inesistente',Db::getInstance()->lastError);
                        }

                    }

                }

                if(!empty($values['id_group']) && !empty($values['id_subgroup']) && !empty($values['id_lvl5']) && !empty($values['id_lvl6']) && !empty($values['id_lvl7'])) {

                    $category_code = trim($values['id_group']).'-'.trim($values['id_subgroup']) .'-'.trim($values['id_lvl5']) .'-'.trim($values['id_lvl6']) .'-'.trim($values['id_lvl7']); ;

                    if(!isset($categories[$category_code])) {
                        
                        Log::error('products_categories','ID cat:' . $category_code .' codice prodotto:' .$code ,'Categoria inesistente');
                    
                    } else {
                        
                        if(!Db::replace('products_categories',['id_products' => $insert['id'], 'id_categories' => $categories[$category_code], 'main' => 1])) {
                            Log::error('products_categories','ID categoria:' . $categories[$category_code] .' codice prodotto:' . $code  ,'Categoria o prodotto inesistente',Db::getInstance()->lastError);
                        }

                    }

                }

            }

      

        }
        
        Db::insert('import_logs',['import' => 'products','date_start' => $start,'date_end' => date("Y-m-d H:i:s")]);
    }

    public static function icons ($return = 'gamma') {

        $fields = [
            'material' => [],
            'tech' => [],
            'discipline' => [],
            'season' => []
        ];

        $icons = [];

        $gamma = [];

        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/tech_icon.json') as $values) {

            if($values['tipologia_attributo'] == 'spec_tech') {
                $values['tipologia_attributo'] = 'tech';
            }

            $icons[$values['codice_attributo']] = $values['tipologia_attributo'];

            $fields[$values['tipologia_attributo']][] = trim($values['codice_attributo']);

            if(!empty($values['codice_icona_gamma'])) {
                $gamma[(string)$values['codice_icona_gamma']] = $values['codice_attributo'];
            }
      

        }

        //TODO: spech_tech era uguale a campo descirttivo, scrivere su documentazione che è cambiato

        //Questo tira fuori opzioni per i campi set del database
        //dump("'".str_replace(',',"','",implode(",", $fields['tech'])). "'");

        if($return == 'gamma') {
            return $gamma;
        }

        return $icons;
    }


}