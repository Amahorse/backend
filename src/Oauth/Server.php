<?php

declare(strict_types=1);

namespace Kodelines\Oauth;

use Kodelines\App;
use Elements\Stores\Stores;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;

class Server
{



    /**
     * Contiene
     */
    public $client = [];
    
    /**
     * Costruisce parametri oauth server in base a 
     */
    public function __construct(Request $request, array $arguments) {

        /**
         * Definisco il token inviato come costante
         */
        define('_OAUTH_TOKEN_',$arguments["token"]);

        /**
         * Definisco il token jti
         */
        define('_OAUTH_TOKEN_JTI_',$arguments['decoded']['jti']);

        /**
         * Definisco il client id
         */
        define('_OAUTH_CLIENT_ID_',$arguments['decoded']['aud']);



        /**
         * Controller per client id 
         */
     
        //TODO: da verificare se risposta con 403 va bene
        if(!empty($arguments['decoded']['sub'])) {
  
            //Utente non più valido 
            if(!$user = User::isValid($arguments)) {

                //Codice 403
                throw new HttpForbiddenException($request,'User not Found');
            } 

            //Il token è valido ma l'utente non ha scope per accedere come utente ad applicazione corrente
            if(!in_array(Scope::name($user["auth"]),config('token','scopes'))) {    
            
                //Codice 403
                throw new HttpForbiddenException($request,'Scope not valid');
            }

            
            //Il token è valido ma la scope del token non è valida per accedere come utente ad applicazioe corrente
            if(!in_array($arguments['decoded']['scope'],config('token','scopes'))) {      
       
                //Codice 401
                throw new HttpUnauthorizedException($request,'Scope not valid');
            }

            $_ENV['user'] = $user;
    
        } else {

            //Se il token non è più valido va chiesto un nuovo refresh token
            if(!defined('_BOT_DETECTED_') && !Token::isValid($arguments["token"],$arguments['decoded']['aud'])) {

                //Codice 403
                throw new HttpForbiddenException($request,'Token revoked');
            }

        }


 
        //Recupero eventuale store da client kid
        if(!empty($arguments['decoded']['kid']) && App::getInstance()->client = Stores::getByKid($arguments['decoded']['kid'])) { 
  
            define('_ID_STORES_',id(App::getInstance()->client['id_stores']));
            
        } else {

            //ID STORE su header ha minore priorità rispetto a quello del token
            if((App::getInstance()->clientHeader = $request->getHeaderLine("X-IdStores")) && App::getInstance()->clientHeader !== 'false' && !empty(id(App::getInstance()->clientHeader))) {
                        
                define('_ID_STORES_',id(App::getInstance()->clientHeader));

                App::getInstance()->config->set('store','id_stores',id(App::getInstance()->clientHeader));
                

            } elseif(($storeDefault = config('store','id_stores',true)) && $storeDefault !== 'false') {

                define('_ID_STORES_',id($storeDefault));
            }

            if(defined('_ID_STORES_') && !App::getInstance()->client = Stores::get(_ID_STORES_,['id_resellers' => false])) {
                //Codice 403
                throw new HttpForbiddenException($request,'Store not found');
            }

        }

     

        /** 
         * Prima hanno la priorità i valori settati su header, poi quelli dello store, poi quelli di default, una volta controllati questi sul before del jwtAuthentication 
         * in base a cosa deve fare la app c'è il controllo per il carrello
         **/

        //CLIENT TYPE B2C B2B HORECA
        if(($clientHeader = $request->getHeaderLine("X-ClientType"))  && !empty($clientHeader)) {
            
            define('_CLIENT_TYPE_',$clientHeader);

            App::getInstance()->config->set('store','type',$clientHeader);

        } elseif(!empty(App::getInstance()->client['type'])) {

            define('_CLIENT_TYPE_',App::getInstance()->client['type']);

            App::getInstance()->config->set('store','type',App::getInstance()->client['type']);

        } elseif($clientDefault = config('store','type',true)) {

            define('_CLIENT_TYPE_',$clientDefault);
        }

      

        //ID RESELLER
        if(($resellerHeader = $request->getHeaderLine("X-IdResellers")) && $resellerHeader !== 'false' && !empty(id($resellerHeader))) { 

            define('_ID_RESELLERS_',id($resellerHeader));

            App::getInstance()->config->set('store','id_resellers',id($resellerHeader));

        } elseif(!empty(App::getInstance()->client['id_resellers'])) {

            define('_ID_RESELLERS_',id(App::getInstance()->client['id_resellers']));

            App::getInstance()->config->set('store','id_resellers',id(App::getInstance()->client['id_resellers']));

        } elseif(($resellerDefault = config('store','id_resellers',true)) && $resellerDefault !== 'false') {

            define('_ID_RESELLERS_',id($resellerDefault));
        }

        //ID AGENTE
        if(($agentHeader = $request->getHeaderLine("X-IdAgents")) && $agentHeader !== 'false'  && !empty(id($agentHeader))) {

            define('_ID_AGENTS_',id($agentHeader));

            App::getInstance()->config->set('store','id_agents',id($agentHeader));

        } elseif(!empty(App::getInstance()->client['id_agents'])) {

            define('_ID_AGENTS_',id(App::getInstance()->client['id_agents']));

            App::getInstance()->config->set('store','id_agents',id(App::getInstance()->client['id_agents']));

        } elseif(($agentDefault = config('store','id_agents',true)) && $agentDefault !== 'false') {

            define('_ID_AGENTS_',id($agentDefault));
        }

        

        //ID NAZIONE
        if(($countryHeader = $request->getHeaderLine("X-IdCountries")) && $countryHeader !== 'false' && !empty(id($countryHeader))) {
           
            define('_ID_COUNTRIES_',id($countryHeader));
            
        } elseif(!empty(App::getInstance()->client['id_countries'])) {

            define('_ID_COUNTRIES_',id(App::getInstance()->client['id_countries']));

        } elseif(($countryDefault = config('default','id_countries',true)) && $countryDefault !== 'false') {
     
            define('_ID_COUNTRIES_',id($countryDefault));
            
        }

        
        //DOMINIO 
        if($domainHeader = $request->getHeaderLine("X-Domain")) {
            define('_APP_DOMAIN_',$domainHeader);
        }


    }

}

?>