<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Tools\Json;
use GuzzleHttp\Client;

class Currencies {

    public static $directory = null;

    public static function file():string {

        if(self::$directory == null) {
            self::$directory == uploads();
        }

        if(file_exists(self::$directory . 'json/conversions.json')) {
            return self::$directory . 'json/conversions.json';
        }

        return _DIR_CONFIG_ . 'conversions.json';
    }

    /**
     * Torna array di conversione
     */
    public static function getArray():array {

        if(!$json = Json::arrayFromFile(self::file())) {
            return [];
        }

        return $json;
    }


    /**
     * Torna array di conversione
     */
    public static function get(string $currency):array|false {

        $values = self::getArray();

        if(!isset($values[$currency])) {
            return false;
        }

        return $values[$currency];
    }

    /**
     * Ritorna tasso di cambio per valuta
     *
     * @param string $from
     * @param string $to
     * @return string|boolean
     */
    public static function getConversionRate(string $from, string $to):float|bool {

        //Fix per uppercase 
        $from = mb_strtoupper($from);

        $to = mb_strtoupper($to);

        $values = self::getArray();

        if(isset($values[$from]) && isset($values[$from][$to])) {
            return $values[$from][$to];
        }

        return 1;
    }

    /**
     * Sincronizza tassi di cambio con banca d'italia
     *
     * DOCS BANCA ITALIA: https://www.bancaditalia.it/compiti/operazioni-cambi/Nuove_Istruzioni_tecnico-operative.pdf
     * DOCS GUZZLE: https://docs.guzzlephp.org/en/stable/psr7.html 
     */
    public static function sync():array {
     
        $client = new Client([
            'base_uri' => 'https://tassidicambio.bancaditalia.it',
        ]);

        $request = $client->get('/terzevalute-wf-web/rest/v1.0/latestRates?lang={}', ['headers' => ['Accept' => 'application/json']]);
        
        if($request->getStatusCode() == 200) {

            $json = [
                'EUR' => [],
                'USD' => [],
                'CHF' => []
            ];

            $array = json_decode($request->getBody()->getContents(),true);

            foreach($array["latestRates"] as $value) {
                
                if(isset($json[$value['isoCode']])) {
                    $json[$value['isoCode']] = ['EUR' => (float)$value["eurRate"], 'USD' => (float)$value["usdRate"]];
                }

            }

            file_put_contents(uploads() . 'json/conversions.json',json_encode($json, JSON_PRETTY_PRINT));

            return $json;
        }

        return [];
    }



}

?>