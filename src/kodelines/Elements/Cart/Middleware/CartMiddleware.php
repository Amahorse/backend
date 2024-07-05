<?php

declare(strict_types=1);

namespace Elements\Cart\Middleware;

use Kodelines\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response;
use Elements\Cart\Cart;

class CartMiddleware implements MiddlewareInterface
{


    /**
     * Contine Xname per gli headers in lettura e scrittura 
     *
     * @var [type]
     */
    private $Xname;



    /**
     * Undocumented function
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        /**
         * Prefisso api x-name
         */
        $this->Xname = config('api','xname');
    
        $_ENV['cart'] = new Cart;

        //Il carrello non ha valori default
        if(($cartHeader = $request->getHeaderLine($this->Xname."IdCart")) && $cartHeader !== 'false' && !empty(id($cartHeader))) {
           $_ENV['cart']->check($cartHeader);
        } else {
           $_ENV['cart']->check();
        }

        if(!empty($_ENV['cart']->order['id'])) {
            define('_ID_STORE_ORDERS_',$_ENV['cart']->order['id']);
        }

        
        return $handler->handle($request);
    }

}

?>