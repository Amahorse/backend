<?php

declare(strict_types=1);

namespace Elements\Products\Helpers;

use Kodelines\Db;


class Type {

    /**
     * Prende le info di un prodotto, il ref id può variare perchè le info prodotto possono essere legate a più tabelle 
     *
     * @param integer $id
     * @param string $type
     * @param string $ref_id
     * @return array
     */
    public static function get(int $id_products, string $type):array {

        if(!Db::tableExists("products_type_".$type)) {
            return [];
        }

        if(!$info = Db::getRow("SELECT * FROM products_type_".$type." WHERE id_products = ". id($id_products))) {
            return [];
        }

        //Prendo anche dati da sotto tipologie tabelle

        //WINE
        if($type == 'wine') {

            $info['aging'] = Db::getArray("SELECT * FROM products_type_wine_aging WHERE id_products = ". id($id_products));

            $info['grapes'] = Db::getArray("SELECT * FROM products_type_wine_grapes WHERE id_products = ". id($id_products));

        }

        //SPIRITS
        if($type == 'drinks') {

            $info['ingredients'] = Db::getArray("SELECT * FROM products_type_drinks_ingredients WHERE id_products = ". id($id_products));
       
            $info['nutritional_values'] = Db::getArray("SELECT * FROM products_type_drinks_nutritional_values WHERE id_products = ". id($id_products));
        }

        return $info;
    }

    /**
     * Faccio replace su tabelle tipo, è chiave unica per id_products quindi non serve funzione di update ma fare un replace 
     * @param integer $id_products
     * @param string $type
     * @param array $values
     * @return bool
     */
    public static function insert(int $id_products, string $type, $values = []) {

        if(!Db::tableExists("products_type_".$type)) {
            return false;
        }

        if(!isset($values[$type])) {
            return false;
        }
    
        $values[$type]['id_products'] = $id_products;
        
        Db::replace("products_type_".$type,$values[$type]);

        if($type == 'wine') {

            if(isset($values['aging'])) {     
                Db::insertMultiple($values['aging'],'products_type_wine_aging', 'id_products', $id_products, Db::getArray("SELECT * FROM products_type_wine_aging WHERE id_products = ". id($id_products)));
            }

            if(isset($values['grapes'])) {
                Db::insertMultiple($values['grapes'],'products_type_wine_grapes', 'id_products', $id_products, Db::getArray("SELECT * FROM products_type_wine_aging WHERE id_products = ". id($id_products)));
            }

        }

        //SPIRITS
        if($type == 'spirits') {

            if(isset($values['ingredients'])) {
                Db::insertMultiple($values['ingredients'],'products_type_drinks_ingredients', 'id_products', $id_products, Db::getArray("SELECT * FROM products_type_drinks_ingredients WHERE id_products = ". id($id_products)));
            }

            if(isset($values['nutritional_values'])) {
                Db::insertMultiple($values['nutritional_values'],'products_type_drinks_nutritional_values', 'id_products', $id_products, Db::getArray("SELECT * FROM products_type_drinks_nutritional_values WHERE id_products = ". id($id_products)));
            }

        }
   
        return true;
    }   

 

}

?>