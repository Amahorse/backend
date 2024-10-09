<?php

declare(strict_types=1);

namespace Kodelines\Oauth;

use Kodelines\Context;
use Slim\Psr7\Request;

class Server
{
    
    /**
     * Costruisce parametri oauth server in base a 
     */
    public function __construct(Request $request, array $arguments) {

        Context::$token = new Token($request,$arguments);

        //NOTA: In teoria i parametri su header sono consentiti solo per admin
        if(($storeHeader = $request->getHeaderLine("X-IdStores")) && !empty($storeHeader)) {            
            define('_ID_STORES_',id($storeHeader));
        } else {
            define('_ID_STORES_', id(user('id_stores') ?: client('id_stores')));
        }
   
        if(($clientHeader = $request->getHeaderLine("X-ClientType")) && !empty($clientHeader)) {
            define('_CLIENT_TYPE_',$clientHeader);
        } else {
            define('_CLIENT_TYPE_',client('type'));
        } 

        if(($resellerHeader = $request->getHeaderLine("X-IdResellers")) && !empty(id($resellerHeader))) { 
            define('_ID_RESELLERS_',id($resellerHeader));
        } elseif (!empty(client('id_resellers'))) {
            define('_ID_RESELLERS_',id(user('id_resellers')));
        } 

        if(($agentHeader = $request->getHeaderLine("X-IdAgents")) && !empty(id($agentHeader))) {
            define('_ID_AGENTS_',id($agentHeader));
        } elseif(!empty(client('id_agents'))) {
            define('_ID_AGENTS_',id(user('id_agents')));
        }

        if(($countryHeader = $request->getHeaderLine("X-IdCountries")) && !empty(id($countryHeader))) {
            define('_ID_COUNTRIES_',id($countryHeader));   
        } else {
            define('_ID_COUNTRIES_',id(client('id_countries') ?: config('store','id_stores')));    
        }
      
        //DOMINIO 
        if($domainHeader = $request->getHeaderLine("X-Domain")) {
            define('_APP_DOMAIN_',$domainHeader);
        }
        

    }

}

?>