<?php

declare(strict_types=1);

namespace Elements\Import;

use Kodelines\Tools\Json;
use Kodelines\Db;
use Elements\Import\Helpers\Log;


class Store
{

    public static function start() {

        
        Db::getInstance()->skipError = true;

        //TODO: Creare stato errore del prodotto e definire quali campi sono fondamentali per l'esportazione e in caso bloccare oppure quelli che si possono saltare

        foreach(Json::arrayFromFile(_DIR_UPLOADS_ .'import/products.json') as $values) {

            //Get Prodotto per codice padre 
            $product = [];


            //COSTRUZIONE SKU
            if(empty($product['variant'])) {
                $values['sku'] = $product['code'];
            } else {
                $values['sku'] = $product['code'] . '_' . trim($values['variant']);
            }
            
            //STATUS
            if($values['ST02_ANNULLATO'] == '1') {

                $values['status'] = 'deleted';

            } else {

                //TODO: tabellla conversione fatta meglio
                //TODO: Prendere availabilty_type da products
                if($values['Stato_variante'] == mb_strtolower('attiva')) {

                    $values['status'] = 'on_sale';

                } elseif($values['Stato_variante'] == mb_strtolower('in esaurimento')) {

                    $values['status'] = 'low_stock';

                    //In esaurimento non può essere su ordinazione
                    $values['availability_type'] = 'warehouse';

                } elseif($values['Stato_variante'] == mb_strtolower('non attiva')) {

                    $values['status'] = 'not_on_sale';

                } else {

                    Log::error('products',$values['variant'],'Errore stato prodotto: ' . $values['Stato_variante'],Db::getInstance()->lastError);

                    continue;

                }


            } 

            //BARCODE
            if(!empty($values['barcode']) && strlen($values['barcode']) > 13) {

                Log::error('products',$values['variant'],'Not valid barcode: ' . $values['barcode'],Db::getInstance()->lastError);

                continue;

            }

            //COLLEZIONE e STAGIONE
            //split campo + divisione estivo e invernale con String Start

        }


        //TODO: avvisami quando disponibile
        //TODO: trigger se prodotto stato = low_stock va a disponiblità 0 lo stato del prodotto è out_of_stock

        //TODO: controllo icona colore svg
        //TODO: store_products se ho un campo variante a1 o a0 etc valorizzato su products ma il prodotto non ha il valore va bloccato e loggato

        //Se a1 o a4 contengono colore regola immagine codice-padre-1-a1-a4.jpg;

        //Descrizione variante = trim di size o color
    }
    

}