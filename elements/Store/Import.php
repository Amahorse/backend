<?php

declare(strict_types=1);

namespace Elements\Store;

use Kodelines\Tools\Json;
use Kodelines\Db;
use Elements\Import\Helpers\Log;
use Elements\Products\Products;
use Kodelines\Helpers\Price;


class Import
{

    public static function start() {

        
        Db::getInstance()->skipError = true;

        $products = Products::getCodes();

        $store = Store::getSkus();

        $new = [];

        $primary = options('store_products','color_primary');

        $secondary = options('store_products','color_secondary');

        //TODO: Creare stato errore del prodotto e definire quali campi sono fondamentali per l'esportazione e in caso bloccare oppure quelli che si possono saltare

        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/store_products.json') as $values) {

            //Get Prodotto per codice padre 
            if(!isset($products[$values['code']])) {

                Log::error('products',$values['code'],'Codice padre non trovato');

                continue;

            }

            $insert = [];

            $insert['id_products'] = $products[$values['code']];

            //TIPO DISPONIBILITA'
            if(!empty((int)$values['availability_type'])) {
                $insert['availability_type'] = 'warehouse';
            } else {
                $insert['availability_type'] = 'order';
            }

            //COSTRUZIONE SKU
            $insert['sku'] = Store::generateSku($values);

            $new[$insert['sku']] = true;
            
            
            //STATUS
            if(mb_strtolower($values['status']) == 'attiva') {

                $insert['status'] = 'on_sale';

            } elseif(mb_strtolower($values['status']) == 'in esaurimento') {

                $insert['status'] = 'low_stock';

            } elseif(mb_strtolower($values['status']) == 'non attiva') {

                $insert['status'] = 'not_on_sale';

            } else {

                Log::error('products',$insert['sku'],'Errore stato prodotto: ' . $values['status']);

                continue;

            }
            

            //BARCODE
            if(!empty($values['barcode']) && strlen($values['barcode']) > 13) {

                Log::error('products',$insert['sku'],'Not valid barcode: ' . $values['barcode']);

            }

            //CAMPI NON MODIFICATI
            $insert['barcode'] = !empty($values['barcode']) ? $values['barcode'] : null;

            $insert['variant'] = !empty($values['variant']) ? $values['variant'] : null;

            $insert['minimum_order'] = !empty($values['minimum_order']) ? $values['minimum_order'] : 1;

            //FILTRO COLORE PRIMARIO E SECONDARIO
            if(!empty($values['color_primary'])) {

                $insert['color_primary'] = '';

                $colors = explode(';',$values['color_primary']);

                foreach($colors as $color) {

                    if(empty($color)) {
                        continue;
                    }

                    if(!in_array($color,$primary)) {
                        
                        Log::error('store_products',$insert['sku'],'Errore colore primario: ' . $color);

                        continue;
                    }

                    if(empty($insert['color_primary'])) {
                        $insert['color_primary'] = $color;
                    } else {
                        $insert['color_primary'] .= ',' . $color;
                    }

                }

            } 

            if(empty($values['color_primary'])) {
                $insert['color_primary'] = NULL;
            }

            if(empty($values['cover'])) {
                $insert['cover'] = NULL;
            } else {
                $insert['cover'] = $values['cover'];
            }

            if(empty($values['cover_url'])) {
                $insert['cover_url'] = NULL;
            } else {
                $insert['cover_url'] = $values['cover_url'];
            }

            if(!empty($values['color_secondary'])) {

                $insert['color_secondary'] = '';

                $colors = explode(';',$values['color_secondary']);

                foreach($colors as $color) {

                    if(empty($color)) {
                        continue;
                    }

                    if(!in_array($color,$secondary)) {
                        Log::error('store_products',$insert['sku'],'Errore colore secondario: ' . $color);

                        continue;
                    }

                    if(empty($insert['color_secondary'])) {
                        $insert['color_secondary'] = $color;
                    } else {
                        $insert['color_secondary'] .= ',' . $color;
                    }

                }

            }

            if(empty($values['color_secondary'])) {
                $insert['color_secondary'] = NULL;
            }

            //CODICI VARIANTE 
            if(!empty($values['a0_code'])) {
                $insert['a0_code'] = $values['a0_code'];
            }

            if(!empty($values['a1_code'])) {
                $insert['a1_code'] = $values['a1_code'];
            }

            if(!empty($values['a4_code'])) {
                $insert['a4_code'] = $values['a4_code'];
            }

            //DESCRIZIONI VARIANTE
            if(!empty($values['a0_description'])) {
                $insert['a0_description'] = $values['a0_description'];
            }

            if(!empty($values['a1_description'])) {
                $insert['a1_description'] = $values['a1_description'];
            }

            if(!empty($values['a4_description'])) {
                $insert['a4_description'] = $values['a4_description'];
            }

            //ORDINE VARIANTE

            if(!empty($values['a0_order'])) {
                $insert['a0_order'] = $values['a0_order'];
            }

            if(!empty($values['a1_order'])) {
                $insert['a1_order'] = $values['a1_order'];
            }

            if(!empty($values['a4_order'])) {
                $insert['a4_order'] = $values['a4_order'];
            }


            //COLLEZIONE e STAGIONE
            if(!empty($values['collection'])) {

                $collections = explode(';',$values['collection']);
     
                foreach($collections as $collection) {

                    $collection = trim($collection);

                    if(empty($collection)) {
                        continue;
                    }

                    if($collection == 'ETC') {
                        $collection = 'ETS22';
                    }

                    if($collection == 'CONT_EST' || $collection == 'CONT_INV') {
                        $collection = 'CONT';
                    }

                    if(!isset($insert['collection'])) {
                        $insert['collection'] = $collection;
                    } else {
                        $insert['collection'] .= ',' . $collection;
                    }
        
                }
            } 



            //split campo + divisione estivo e invernale con String Start

            
            if(!isset($store[$insert['sku']])) {

                if(!$insert['id'] = Db::insert('store_products',$insert)) {

                    Log::error('store_products',$insert['sku'],'Errore inserimento variante',Db::getInstance()->lastError);

                    continue;
                }

            } else {

                $insert['id'] = $store[$insert['sku']];

                if(!Db::updateArray('store_products',$insert,'id',$insert['id'])) {

                    Log::error('store_products',$insert['sku'],'Errore aggiornamento variante',Db::getInstance()->lastError);

                }

            }
            
          
            
        
            //SCONTI
            if(!empty($values['discount_percentage'])) {
                    
                    $discount = [
                        'imported' => 1
                    ];
    
                    $discount['id_store_products'] = $insert['id'];
    
                    $discount['discount_percentage'] = $values['discount_percentage'];
    
                    $discount['date_start'] = str_replace('T',' ',$values['date_start']);
    
                    $discount['date_end'] = str_replace('T',' ',$values['date_end']);
    
                    if(!Db::replace('store_products_discounts',$discount)) {
    
                        Log::error('store_products_discounts',$insert['sku'],'Errore inserimento sconto',Db::getInstance()->lastError);
    
                    }
            }

        }


        foreach($store as $sku => $id) {

            if(!isset($new[$sku])) {

                if(!Db::update('store_products','status','deleted','sku',$sku)) {

                    Log::error('store_products',$sku,'Errore status annullato variante',Db::getInstance()->lastError);

                }

            }

        }

        //TODO: status annullato se non c'è prodotto
        //TODO: availability_b2c etc su altra tabella perchè sennò date_update non combacia e probabilmente disponibilità andrà per magazzino fisico (parlare con mattia)
        //TODO: store_products se ho un campo variante a1 o a0 etc valorizzato su products ma il prodotto non ha il valore va bloccato e loggato
        //TODO: campo new
        //TODO: controllo icona colore svg
        //TODO: avvisami quando disponibile
        //TODO: trigger se prodotto stato = low_stock va a disponiblità 0 lo stato del prodotto è out_of_stock
        //TODO: da definire campo season
        //Se a1 o a4 contengono colore regola immagine codice-padre-1-a1-a4.jpg;

        //Descrizione variante = trim di size o color
        //TODO: endpoit su store_products_images per darle a luca (nome file, url, code, variant, date) + cover products
        //TODO: aggiungere progressivo per sku su immagini 
    }


    public static function prices() {

        Db::getInstance()->skipError = true;

        $store = Store::getSkus();

        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/store_products_prices.json') as $values) {

            $sku = Store::generateSku($values);

            if(!isset($store[$sku])) {

                Log::error('store_products_prices',$sku,'Codice prodotto non trovato');

                continue;

            }
          
            foreach($values['prices'] as $key => $value) {

                $insert = [
                    'id_stores' => $key,
                    'id_store_products' => id($store[$sku]),
                    'price' => Price::format($value)
                ];
           
                
                if(!Db::replace('store_products_prices',$insert)) {
    
                    Log::error('store_products_prices',$sku,'Errore inserimento prezzo',Db::getInstance()->lastError);

                }

            }

        }

    }

    public static function availabilities() {

        Db::getInstance()->skipError = true;

        $store = Store::getSkus();

        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/store_products_availability.json') as $values) {

            $sku = Store::generateSku($values);

            if(!isset($store[$sku])) {

                Log::error('store_products_availability',$sku,'Codice prodotto non trovato');

                continue;

            }

            $insert = [
                'id_store_products' => id($store[$sku]),
                'availability_b2c' => $values['availability_b2c'],
                'availability_b2b' => $values['availability_b2b'],
            ];
 
            if(!Db::replace('store_products_availability',$insert)) {

                Log::error('store_products_availability',$sku,'Errore inserimento disponibilità',Db::getInstance()->lastError);

            }

            

        }

    }


    public static function images() {

    }
    

}