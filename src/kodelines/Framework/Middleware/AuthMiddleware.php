<?php

declare(strict_types=1);

namespace Kodelines\Middleware;

use Kodelines\App;
use Kodelines\Oauth\Scope;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response;
use Slim\Exception\HttpForbiddenException;


/**
 * Slim 4 Base path middleware.
 */
class AuthMiddleware implements MiddlewareInterface
{

    /**
     * Livello minimo accesso applicazione
     */
    private $min_level = "guest";

    /**
     * Lista accessi abilitati applicazione
     */
    private $scopes = [];

    /** 
     *  sul constructor definisco auth
     */
    public function __construct($args = [])
    {
        
        //di default gli scope sono su config
        $this->scopes = config('token','scopes');

        //Array vuol dire solo quelli sono accettati
        if(!empty($args) && is_array($args)) {
            $this->scopes = $args;
        }

        //Str
        if(!empty($args) && is_string($args)) {
            $this->min_level = $args; 
        }


    }


    /**
     * Example middleware invokable class
     *
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        
        $response = $handler->handle($request);
       
        //Non c'è utente e accesso è ristretto a guest
        if(empty($_ENV['user']) && $this->min_level <> 'guest') {   
            throw new HttpForbiddenException($request,'access_denied');
        }
  
        if(!empty($this->scopes) && !in_array(Scope::name($_ENV['user']["auth"]),$this->scopes)) {           
            throw new HttpForbiddenException($request,'access_denied');
        }

        if((int)$_ENV['user']["auth"] < Scope::code($this->min_level)) {      
            throw new HttpForbiddenException($request,'access_denied');
        }
       
      

        return $response;

    }

}

?>