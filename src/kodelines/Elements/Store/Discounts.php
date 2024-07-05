<?php

declare(strict_types=1);

namespace Elements\Store;

use Kodelines\Db;
use Kodelines\Tools\Str;
use Kodelines\Abstract\Decorator;
use Kodelines\Exception\ValidatorException;

class Discounts extends Decorator  {

    /**
     * Fa tutti i controlli e applica un coupon
     *
     * @param string $discount_code
     * @param array $order
     * @return void
     */
    public static function apply(string $discount_code, array $order) {

        //Prevengo operazioni su ordine già confermato
        if($order['status'] == 'confirmed' || $order['status'] == 'deleted') {
            throw new ValidatorException('order_is_already_confirmed');
        }
        
        if(!$coupon = self::checkCode($discount_code)) {
            throw new ValidatorException('discount_code_not_valid');
        }

        if(!self::checkValidity($coupon)) {
            throw new ValidatorException('discount_code_already_used');
        }

        //Controllo validità coupon per reseller o store principale
        if(defined('_ID_RESELLERS_')) {

            if(empty($coupon['id_resellers']) || $coupon['id_resellers'] <> _ID_RESELLERS_) {
                throw new ValidatorException('discount_code_not_valid');
            }

        } else {

            if(!empty($coupon['id_resellers'])) {
                throw new ValidatorException(tr('discount_another_reseller') . ': <a href="' . $coupon['reseller_url'] . '">' . $coupon['reseller_url'] . '</a>');
            }

        }
        
        if(!empty($order['total_discounts_products']) && $coupon['cumulable'] == 0) {
            throw new ValidatorException('discount_not_cumulable');
        }
        
        //TODO: il client type va messo come set e non come all e i filtri direttamente in query
        if(empty($order['type']) || ($coupon['client_type'] <> 'all' && $coupon['client_type'] <> $order['type'])) {  
            throw new ValidatorException('discount_not_applicable');
        }
      
        if(empty($order['total_to_pay']) || $coupon['discount_minimum_amount'] > $order['total_to_pay']) {
            throw new ValidatorException('discount_not_applicable');
        }

    //TODO: questo è brutto, va evitato di rifare questa cosa due volte e controllato per bene tutto il processo di coupon
      //Rifaccio controllo se coupon se valido  per ordine o per 
      if(!self::checkCode($discount_code,'order')) {

        if(!Db::query("UPDATE store_orders SET discount_code = " . Db::encode($discount_code). " WHERE id = " . id($order['id']))) {
            throw new ValidatorException('database_error');
        }

      } else {

        if(!Db::query("UPDATE store_orders SET discount_code = " . Db::encode($discount_code). ", discount_percentage = " . Db::encode($coupon['discount_percentage']). ", discount_cumulable = ".id($coupon['cumulable'])." WHERE id = " . id($order['id']))) {
            throw new ValidatorException('database_error');
        }

      }

    }

    /**
     * Resetta un coupon
     *
     * @param array $order
     * @return void
     */
    public static function reset(array $order) {

        //Prevengo operazioni su ordine già confermato
        if($order['status'] == 'confirmed' || $order['status'] == 'deleted') {
            throw new ValidatorException('order_is_already_confirmed');
        }
        

        if(!Db::query("UPDATE store_orders SET discount_code = NULL, discount_percentage = NULL, discount_cumulable = 0 WHERE id = " .id($order['id']))) {
            throw new ValidatorException('database_error');
        }

    }

    /**
     * Prende coupon in base a codice 
     *
     * @param string $discount_code
     * @return array|boolean
     */
    public static function getFromCode(string $discount_code):array|bool {

        $filters = ['type' => 'coupon','discount_code' => $discount_code];

        $query = self::query($filters);
        
        return Db::getRow($query);

    }

    /**
     * Prende coupon in base a codice e ne controlla validità 
     *
     * @param string $discount_code
     * @return array|boolean
     */
    public static function checkCode(string $discount_code, string $mode = null):array|bool {

        if($mode !== null) {
            $filters['mode'] = $mode;
        }

        $filters = ['type' => 'coupon','discount_code' => $discount_code, 'valid' => true, 'status' => 1];

        if($mode !== null) {
            $filters['mode'] = $mode;
        }

        $query = self::query($filters);
        
        return Db::getRow($query);

    }
    

    /**
     * Controlla se un utente può usare un coupon
     *
     * @param integer $user
     * @param integer $id
     * @return boolean
     */
    public static function checkValidity(array $coupon):bool {

        if($coupon['reusable'] == 'not_same_user') {

            if(user() && Db::getValue("SELECT store_discounts.reusable FROM store_discounts_users JOIN store_discounts ON store_discounts.id = store_discounts_users.id_store_discounts WHERE store_discounts_users.id_users = ".id(user('id')). "  AND id_store_discounts = " . id($coupon['id'])  . " LIMIT 1 ")) {
                return false;
            }

        }

        if($coupon['reusable'] == 'no') {

            if(Db::getValue("SELECT store_discounts.reusable FROM store_discounts_users JOIN store_discounts ON store_discounts.id = store_discounts_users.id_store_discounts WHERE id_store_discounts = " . id($coupon['id']) . " LIMIT 1 ")) {
                return false;
            }

        }

        return true;

    }

    /**
     * Genera codici sconto random
     */
    /* INTESA:

    [
    'name' => 'Promo Isybank',
    'status' => 1,
    'cumulable' => 0,
    'reusable' => 'no',
    'type' => 'coupon',
    'mode' => 'order,shipping',
    'date_start' => '2024-04-18',
    'date_end' => '2024-09-30',
    'discount_minimum_amount' => 0,
    'discount_percentage' => 100,
    'id_resellers' => 21,
    'client_type' => 'b2c'
    ]

    */
    public static function generateRandom(int $quantity, int $lenght, array $values, $prefix = ''):void {

        $check = 0;

        while($check < $quantity) {

            $values['discount_code'] = $prefix . mb_strtoupper(Str::random($lenght));

            if(!Db::getValue("SELECT discount_code FROM store_discounts WHERE discount_code = " . encode($values['discount_code']))) {

                Db::insert('store_discounts',$values);

                $check++;
            }


        }

    }


}

?>