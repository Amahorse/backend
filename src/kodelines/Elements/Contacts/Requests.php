<?php

declare(strict_types=1);

namespace Elements\Contacts;

use Kodelines\Abstract\Decorator;
use Kodelines\Tools\Str;
use Elements\Tracking\Ban;

class Requests extends Decorator  {

    /**
     * Parsa dati extra dal database per non visualizzarli doppi
     *
     * @param array $values
     * @return array
     */
    public static function extraData(array $values): array {

        $standard = ['id','type','email','name','first_name','last_name','status','username','business_name','vat_number','form','date_ins','date_update','phone','subject','message'];

        $extra = [];

        foreach($values as $key => $value) {

            if(!empty($value) && !in_array($key,$standard) && !Str::startsWith($key,'id_') &&  !Str::startsWith($key,'tracking_')) {
                $extra[$key] = $value;
            }

        }

        return $extra;
    }


 

    /**
     * Controlla dati con regole di sicurezza per blocco ip
     *
     * @param string $email
     * @return boolean
     */
    public static function checkData(array $data): bool {

        
        if((!empty($data['email']) && Str::endsWith(Str::minify($data['email']),'.ru')) || !Ban::checkIP()) {

            Ban::IP();

            return false;
        }


        if(!empty($data['first_name']) && !empty($data['last_name']) && ($data['first_name'] == $data['last_name'])) {

            Ban::IP();

            return false;
        }

        return true;
    }



}

?>