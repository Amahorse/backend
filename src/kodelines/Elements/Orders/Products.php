<?php

declare(strict_types=1);

namespace Elements\Orders;

use Kodelines\Abstract\Decorator;
use Kodelines\Exception\ValidatorException;
use Kodelines\Db;
use Kodelines\Tools\File;
use Elements\Store\Store;
use Elements\Store\Warehouse;
use Elements\Store\Tax;
use Elements\Products\Helpers\Type;


class Products extends Decorator  {


/**
 * Get del prodotto completo con controllo se appartiene a ordine
 *
 * @param integer $id
 * @param array $order
 * @return array|boolean
 */
public static function fullList(array $filters):array|bool {

   $products = [];

   //Questo serve a recuperare i prodotti principali e non i parent
   if(empty($filters['id_store_orders_products_parent'])) {
     $filters['id_store_orders_products_parent'] = null;
   }


   foreach(self::list($filters) as $product) {

    if(!empty($product['id_product'])) {
        $product['type_data'] = Type::get($product['id_products'],$product['type']);
    } else {
        $product['type_data'] = [];
    }
   

    //Questo serve a recuperare i sottoprodotti
    if(empty($product['id_store_orders_products_parent'])) {
        $product['components'] = self::fullList(['id_store_orders_products_parent' => $product['id']]);
    } else {
        $product['components'] = [];
    }

    $products[] = $product;
    
   }

   return $products;
}  

/**
 * Get del prodotto completo con controllo se appartiene a ordine
 *
 * @param integer $id
 * @param array $order
 * @return array|boolean
 */
public static function getAndCheck(int $id, array $order):array|bool {

    if(empty($order) || !$product = self::fullGet($id)) {
        return false;
    }

    if($product['id_store_orders'] <> $order['id']) {
        return false;
    }

    return $product;
}


 /**
  * Questa funzione è la più importante e complessa di tutto il sistema perchè può tornare tutti i tipi di podotto in base alle specifiche fornite su array $values che 
  * possono provenire da più parti (store, configurator o store_orders) e contenere varie cose diverse da fare ma ritorna il prodotto sempre uguale per essere inserito in store_orders_products
  *
  * @param array $order
  * @param array $values richiesti = id_store_products o id_configurator, listing, quantity è opzionali
  * @param int $price se false non genera il prezzo del prodotto sulla classe store
  * @return array
  */
  public static function generate(array $values,array $order = [],$price = true):array {
    
 
    //Questo dovrebbero farlo a monte le classi front o listino ma faccio un ulteriore controllo in quanto definendo l'id ordine prima di prendere i prodotti
    if(!empty($order['id'])) {
        Store::getInstance()->setOrder($order['id']);
    }

    if(!empty($order['id_countries'])) {
        Store::getInstance()->setCountry($order['id_countries']);
    }

    if(!empty($values['quantity'])) {

        //Faccio un cast a intero di quantità per non dare errori nelle funzioni successive
        $values['quantity'] = (int)$values['quantity'];

        Store::getInstance()->setQuantity($values['quantity']);
    } 

    if(!empty($values['id_store_tax'])) {
        Store::getInstance()->setTax($values['id_store_tax']);
    }

    if(!empty($values['listing'])) {

        //Prodotto listino configuratore prende dati su classe specifica diversa dalle altre ma ritorna stessi valori calcolati
        if($values['listing'] == 'configurator') {

            if(empty($values['id_configurator'])) { 
                throw new ValidatorException('product_not_set');
            }

            if(!empty($order['id'])) {
                \Elements\Configurator\Configurator::setOrder($order['id']);
            }

            if(!empty($order['id_countries'])) {
                \Elements\Configurator\Configurator::setCountry($order['id_countries']);
            }

            if(!empty($values['id_store_tax'])) {
                \Elements\Configurator\Configurator::setTax($values['id_store_tax']);
            }

            if(!empty($values['id_printing_front'])) {
                \Elements\Configurator\Configurator::setPrintingFront($values['id_printing_front']);
            }

            if(!empty($values['id_printing_back'])) {
                \Elements\Configurator\Configurator::setPrintingBack($values['id_printing_back']);
            }

            if(!empty($values['quantity'])) {
                \Elements\Configurator\Configurator::setQuantity($values['quantity']);
            } 
     
            if(!$product = \Elements\Configurator\Configurator::get($values['id_configurator'])) {
                throw new ValidatorException('product_not_found');
            }
            
            //Se è forzato ricalcolo prezzo in base a valori passati dal form e sovrascrivo quelli del prodotto
            if(!empty($values['forced'])) {
      
                //Resetto gli sconti su prezzo iva esclusa che su forzatura vanno ricalcolati
                if(!empty($values['price_final_taxes_excluded'])) {
                    $values['price_taxes_excluded'] = $values['price_final_taxes_excluded'];
                }

                if(!empty($values['discounts_total_percentage'])) {
                    $values['discounts_total_percentage'] = 0;
                }

           

                if(!empty($values['price_discount'])) {
                    $values['price_discount'] = 0;
                }
                
          
                $product = \Elements\Configurator\Configurator::calculator()->generate(array_merge($product,$values));
                
            } else {

                $product['forced'] = 0;

            }
            

            //Forzatura in caso di errori da front end
            $product['id_store_products'] = NULL;

                
        //Prodotto ibrido configurator_preset è estensione classe store ma prende dati anche da configuratore
        } elseif($values['listing'] == 'custom') {

            if(!empty($values['id_store_tax']) && $taxes = Tax::get(id($values['id_store_tax']))) {
                $values['tax'] = $taxes['tax'];
            }

            $product = Store::getInstance()->generate($values);
      
            //Forzatura in caso di errori da front end
            $product['id_configurator'] = NULL;

            //Forzatura in caso di errori da front end
            $product['id_store_products'] = NULL;

        //Altro listino specificato potrebbe ritornari valori specifici del prodotto
        } else {           
       
            if(empty($values['id_store_products'])) {
                throw new ValidatorException('product_not_set');
            }

            if(!empty($order['id'])) {
                Store::getInstance()->setOrder($order['id']);
            }

            if(!empty($order['id_countries'])) {
                Store::getInstance()->setCountry($order['id_countries']);
            }

            if(!empty($values['id_store_tax'])) {
                Store::getInstance()->setTax($values['id_store_tax']);
            }

            if(!empty($values['quantity'])) {
                Store::getInstance()->setQuantity($values['quantity']);
            } 
           
         
            if(!$product = Store::getInstance()->get($values['id_store_products'],$price)) {      
                throw new ValidatorException('product_not_found');
            }

            //Forzatura in caso di errori da front end
            $product['id_configurator'] = NULL;

        }

    } else {

        if(empty($values['id_store_products'])) {
            throw new ValidatorException('product_not_set');
        }


        //Listino non specificato torna 
        if(!$product = Store::getInstance()->get($values['id_store_products'],$price)) {
            throw new ValidatorException('product_not_found');
        }

        //Forzatura in caso di errori da front end
        $product['id_configurator'] = NULL;

    }
    
    //Se è forzato ricalcolo prezzo in base a valori passati dal form e sovrascrivo quelli del prodotto saltando configuratore perchè già fatto prima
    //Il merge sulla chiamata serve a far rimanere i dati del prodotto base che non sono cambiati
    if(isset($values['forced']) && $values['forced'] == 1 && $values['listing'] !== 'configurator') {

        //Resetto gli sconti su prezzo iva esclusa che su forzatura vanno ricalcolati
        if(!empty($values['price_final_taxes_excluded'])) {
            $values['price_taxes_excluded'] = $values['price_final_taxes_excluded'];
        }
        
        /*
        if(!empty($values['discounts_total_percentage'])) {
            $values['discounts_total_percentage'] = 0;
        }

        if(!empty($values['discounts_cart_percentage'])) {
            $values['discounts_cart_percentage'] = 0;
        }
        */

        if(!empty($values['price_discount'])) {
            $values['price_discount'] = 0;
        }
        

        $product = array_merge($product,Store::getInstance()->generate(array_merge($product,$values)));

    }

    //Il custom text va se,ère controllato e preso dai values 
    if(isset($values['custom_text'])) {
        $product['custom_text'] = $values['custom_text'];
    }

    //L'etichetta del prodotto e il template, se custom sovrascrivono sempre quella default
    if(!empty($values['id_labels'])) {
        $product['id_labels'] = $values['id_labels'];
    }

    //L'etichetta del prodotto e il template, se custom sovrascrivono sempre quella default
    if(!empty($values['id_templates'])) {
        $product['id_templates'] = $values['id_templates'];
    }
    
    //Fix per uniformità
    $product['components'] = [];
    
    return $product;

 }

    
    
 /**
  * Aggiunge prodotto a un ordine 
  *
  * @param array $order
  * @param array $values
  * @param bool  $dry in modalità dry torna dati del prodotto senza aggiungerlo al carrello e senza ritornare data order
  * @return array
  */
  public static function add(array $values, array $order = [],  $dry = true):array {
   
    //Prevengo operazioni su ordine già confermato
    if(!empty($order['status']) && ($order['status'] == 'confirmed' || $order['status'] == 'deleted' || $order['status'] == 'completed')) {
        throw new ValidatorException('order_is_already_confirmed');
    }
    

    $product = self::generate($values,$order);
  
    //Modalità dry o id ordine vuoto torna dati del prodotto e basta senza aggiungere niente sul database 
    if($dry == true || empty($order)) {
        return $product;
    }

    $product['id_store_orders'] = $order['id'];

    //Faccio upload di preview se settata e non è immagine 
    //TODO: ripristinare script che pulisce immagini carrello non utilizzate perchè questo non fa altro che cambiarla ogni volta
    if(!empty($values['preview']) && !File::isImage($values['preview'])) {

        $uploaded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $values['preview']));
  
        $file = uniqid('cart-') . '.png';
  
        if(is_writable(uploads()  . 'carts/') && file_put_contents(uploads()  . 'carts/' . $file,$uploaded)) {
          $product['preview'] = $file;
        }
  
    }
   
    //Controllo se il prodotto è già nell'ordine cosi invece di aggiungere aggiorno il precedente
    if(!$id = self::isInOrder($order['id'],$product)) {
       
        //Inserisco in aggiornamento ordine
        if(!$product = self::create($product)) {
            throw new ValidatorException('cart_error');
        }

        //Prodotto pacchetto genera componenti fissi
        if($product['format'] == 'package' || $product['listing'] == 'packages') {
            $product['components'] = Package::generate($product,$order);
        } 


    } else {
        
        //Quantità 0 passata ai valori è sicuro una selettore quanttà e rimuovo dal carrello
        if($values['quantity'] == 0) {
          return self::remove(id($id),$order);       
        }
        
        //Inserisco in aggiornamento ordine
        if(!$product = self::update($id, $product)) { 
            throw new ValidatorException('cart_error');
        }

        //Prodotto pacchetto genera componenti fissi
        if($product['format'] == 'package' || $product['listing'] == 'packages') {
            $product['components'] = Package::update(self::fullGet($id),$order);
        } 

    }

    return $product;

 }

   /**
  * Cambia prodotto su ordine
  *
  * @param integer $id_store_orders
  * @param array $values
  * @param bool  $dry in modalità dry torna dati del prodotto senza aggiungerlo al carrello e senza ritornare data order
  * @return array
  */
  public static function edit(int $id_store_orders_products, array $values, array $order):array {
 
    //Prevengo operazioni su ordine già confermato
    if(!empty($order['status']) && ($order['status'] == 'confirmed' || $order['status'] == 'deleted' || $order['status'] == 'completed')) {
        throw new ValidatorException('order_is_already_confirmed');
    }
    

    if(!$product = self::fullGet($id_store_orders_products)) {
        throw new ValidatorException('not_found');
    }


    //Controllo se il prodotto è il componente di un'altro e in caso moltiplico quantità e opzioni
    if(!empty($product['id_store_orders_products_parent'])) {

        $main = self::get($product['id_store_orders_products_parent']);

        //Moltiplico quantità per prodotto main se segui quantità prodotto principale è attivo
        if(!empty($product['follow_quantity'])) {
            $values['quantity'] = $main['quantity'];
        } 

        $product = self::generate($values,['id' => $product['id_store_orders']],!empty($product['add_price']));

    } else {

        //è brutto ma Devo dichiararlo due volte sennò su generate si perde
        $components = $product['components'];

        $product = self::generate($values,['id' => $product['id_store_orders']]);

        $product['components'] = $components;
          
    }
    

    //Inserisco in aggiornamento ordine
    if(!$product = self::update($id_store_orders_products,$product)) {
        throw new ValidatorException('cart_error');
    }

    //Qui aggiorna tutti i componenti pacchetto perchè non fa differenza se sono custom o no
    $product['components'] = Package::update($product,$order);

    return $product;

  }
/**
  * Elimina prodotto da ordine
  *
  * @param integer $id_store_orders
  * @param array $values
  * @param bool  $dry in modalità dry torna dati del prodotto senza aggiungerlo al carrello e senza ritornare data order
  * @return array
  */
  public static function remove(int $id_store_orders_products, array $order):array {

    //Prevengo operazioni su ordine già confermato
    if(!empty($order['status']) && ($order['status'] == 'confirmed' || $order['status'] == 'deleted' || $order['status'] == 'completed')) {
        throw new ValidatorException('order_is_already_confirmed');
    }
    
  
    if(!$product = self::fullGet($id_store_orders_products)) { 
        throw new ValidatorException('not_found_exception');
    }

    if(!self::delete($id_store_orders_products)) {
        throw new ValidatorException('cart_error');
    }

    //Deve tornare un array con vari id che sono anche quelli dei vari componenti associati per rimuoverli graficamente da listino o checkout
    $products = [$product['id']];

    if(!empty($product['components'])) {
        foreach($product['components'] as $component) {
            $products = [$component['id']];
        }
    }

    return $products;

  }

    /**
     * Controlla se un id prodotto carrello corrisponde all'id di un carrello
     * @param integer $id_store_orders
     * @param integer $id_store_orders
     * @return boolean
     */
    public static function checkOrder(int $id_store_orders, int $id_store_orders_products):bool {

        return Db::getRow("SELECT id FROM store_orders_products WHERE id = " . id($id_store_orders_products) . " AND id_store_orders = " .id($id_store_orders)) ? true : false;

    }
   

    /**
     * Controlla se un prodotto è già nel carrello e restituisce l'id cosi fa update invece di inserire
     * NB: non si può fare replace nel database che ci sarebbero chiavi univoche multiple troppo variabili per tipo listino
     * quindi vanno fatte query separate per controllare campi in base a tipo listino
     *
     * @param integer $id_store_orders
     * @param array $values
     * @return boolean
     */
    public static function isInOrder(int $id_store_orders, array $values):int|bool {


        if(empty($values['id_store_orders_products_parent'])) {
            $query = "SELECT id FROM store_orders_products WHERE id_store_orders = " . $id_store_orders . " AND id_store_orders_products_parent IS NULL ";
        } else {
            $query = "SELECT id FROM store_orders_products WHERE id_store_orders = " . $id_store_orders . " AND id_store_orders_products_parent = " . id($values['id_store_orders_products_parent']);
        }

        if($values['listing'] == 'custom') {

            return false;

        } elseif($values['listing'] == 'configurator') {
            //Prodotto listino configuratore prende dati su classe specifica diversa dalle altre ma ritorna stessi valori calcolati
            //TODO: da gestire quando massimo gestirà carrello nel configuratore
            return false;

            if(!empty($values['id_labels'])) {
                return Db::getValue($query . " AND id_configurator = ".id($values['id_configurator'])." AND id_store_products IS NULL AND id_labels = " . $values['id_labels']);
            } else { 
                return Db::getValue($query . " AND id_configurator = ".id($values['id_configurator'])." AND id_store_products IS NULL AND id_labels IS NULL ");
            }
            
     
        } elseif($values['listing'] == 'configurator_preset') {
            

            if(!empty($values['id_labels'])) { 
                return Db::getValue($query . " AND id_store_products = ".id($values['id_store_products'])."  AND id_printing_front = ".id($values['id_printing_front'])." AND id_printing_back = ".id($values['id_printing_back'])." AND id_labels = " . id($values['id_labels']) . " LIMIT 1");
            } else { 
                //TODO: da gestire quando massimo gestirà carrello nel configuratore
                return false;

                //return Db::getValue($query . " AND id_store_products = ".id($values['id_store_products'])."  AND id_printing_front = ".id($values['id_printing_front'])." AND id_printing_back = ".id($values['id_printing_back'])." AND id_labels IS NULL LIMIT 1");
            }
              
        
        } else {

            return Db::getValue($query . " AND id_store_products = " . $values['id_store_products']);

        }


        return false;
    }


  /**
   * Aggiunge componente a prodotto in ordine
   *
   * @param array $main
   * @param array $component
   * @param [type] $order
   * @return array
   */
  public static function addComponent(array $main, array $component, array $order):array|bool {

    //Prevengo operazioni su ordine già confermato
    if($order['status'] == 'confirmed' || $order['status'] == 'deleted') {
        throw new ValidatorException('order_is_already_confirmed');
    }
        
   
    if(empty($component['id_store_products'])) {
        return false;
    }

    //Ciclo i prodotti all'interno del magazzino
    if(!$product = Warehouse::get($component['id_store_products'])) {  
        return false;
    }

    //Moltiplico quantità per prodotto main se segui quantità prodotto principale è attivo
    if(!empty($component['follow_quantity'])) {
        $product['quantity'] = $main['quantity'];
    } else {
        $product['quantity'] = $component['quantity'];
    }


    $product = self::generate($product,$order,(isset($component['add_price']) && !empty($component['add_price'])));
 
    $product['id_store_orders_products_parent'] = $main['id'];

    $product['id_store_orders'] = $order['id'];

    if(isset($component['follow_quantity']) && !empty($component['follow_quantity'])) {
        $product['follow_quantity'] = 1;
    } else {
        $product['follow_quantity'] = 0;
    }

    if(isset($component['fixed_component']) && !empty($component['fixed_component'])) {
        $product['fixed_component'] = 1;
    } else {
        $product['fixed_component'] = 0;
    }

    if(isset($component['add_price']) && !empty($component['add_price'])) {
        $product['add_price'] = 1;
    } else {
        $product['add_price'] = 0;
    }
    
    if(isset($component['custom_text'])) {
        $product['custom_text'] = $component['custom_text'];
    }
  
    //Inserisco in aggiornamento ordine o aggiorno se id già settato
    if(!empty($component['id'])) {

        if(!$product = self::update($component['id'],$product)) {
            return false;
        }
    
    } else {
       
        if(!$product = self::create($product)) {
            return false;
        }
    
    }


    return $product;
  }


   
}

?>