<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Weight
{
    /**
     * Undocumented function
     *
     * @param integer $weight
     * @param integer $quantity
     * @return void
     */
    public static function display(int $weight,int $quantity = 1) {

        if(empty($weight)) {
          return '';
        }
  
        $weight = $weight * $quantity;
  
        if($weight >= 1000) {
          return $weight / 1000 . ' kg';
        } else {
          return $weight . ' gr';
        }
  
    }

}

?>