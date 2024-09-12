<?php

declare(strict_types=1);

namespace Elements\Categories;

use Kodelines\Abstract\Decorator;
use Kodelines\Db;

class Categories extends Decorator  {


    public static function getUniqId(array $values):string {

        if(empty($values['id_parent_category']) || $values['id_parent_category'] == '0') {
            return $insert['uniqid'] = trim($values['id_category']);
        } 
        
        return  $insert['uniqid'] = trim($values['id_parent_category']) . '-' . trim($values['id_category']);
        
    }


    public static function getCodes():array {

        $exists = [];

        foreach(Db::getArray("SELECT id,uniqid FROM categories") as $value) {
            $exists[$value['uniqid']] = $value['id'];
        }
        
        return $exists;
    }


}

?>