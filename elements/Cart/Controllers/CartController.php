<?php

declare(strict_types=1);

namespace Elements\Cart\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Kodelines\Abstract\Controller;
use Elements\Cart\Cart;
use Elements\Orders\Orders;
use Elements\Orders\Products;


class CartController extends Controller {

  /**
   * Questo c'è nel caso fallisse il middleware per qualche motivo
   */
  public function __construct(ContainerInterface $container)
  {
    
    //Chiamo il constructor parent
    parent::__construct($container);

    //Fa la stessa cosa praticamente anche su CartMiddleware e dovrebbe esserci già il carrello
    if(empty($_ENV['cart'])) {

      //Se non supera controlli o non ha token o roba varia crea carrello da nuovo
     $_ENV['cart'] = new Cart;

    }

  }


  /**
   * Ritorna carrello corrente
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function get(Request $request, Response $response, $args) : Response {

    if(empty($_ENV['cart']->order)) {
      return $this->response($response,[]);
    }

    //Se c'è nazione in get, faccio un ricalcolo dry dell'ordine per vedere i nuovi prezzi
    //TODO: funzione secondo me inutile richiesta da Massimo per checkout
    if(!empty($this->data['id_countries']) && !empty($_ENV['cart']->order)) {

      $order =$_ENV['cart']->order;

      $order['id_countries'] = $this->data['id_countries'];

      return $this->response($response,Orders::refresh($order,true,true));
    }

    return $this->response($response,$_ENV['cart']->order);

  }



  /**
   * Crea carrello, sovrascrive quello per token corrente
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function create(Request $request, Response $response) : Response {

    if(!defined('_OAUTH_TOKEN_JTI_')) {
      throw new HttpBadRequestException($request,'Token not Found');
    }
 
    return $this->response($response,$_ENV['cart']->create());

  }


  /**
   * L'update fa prima il controllo del vecchio stato e del nuovo
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function update(Request $request, Response $response, $args) : Response {

    if(empty($_ENV['cart']->order)) {
      throw new HttpBadRequestException($request,'cart_not_found');
    }

    if(!empty($this->data['status'])) {
      throw new HttpBadRequestException($request,'cant_update_cart_status');
    }

    //Aggiorno ordine
    Orders::update($_ENV['cart']->order['id'],$this->data);

    //Se è cambiata nazione forzo il refresh
    if(!empty($this->data['id_countries']) && $this->data['id_countries'] <>$_ENV['cart']->order['id_countries']) {
      Orders::refresh($_ENV['cart']->order);
    }

    return $this->response($response,$this->fullGet($request,$response));

  }


  

  
  /**
   * Ritorna ordine e lista di prodotti
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function fullGet(Request $request, Response $response) : Response {

    if(empty($_ENV['cart']->order))  {
      return $this->response($response,[]);
    }

    $order = Orders::refresh($_ENV['cart']->order,(isset($this->data['refresh']) && $this->data['refresh'] == 'true'));

    $order['products'] = Products::fullList(['id_store_orders' =>$_ENV['cart']->order['id']]);

    return $this->response($response,$order);

  }

  /**
   * Ritorna lista di prodotti
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function products(Request $request, Response $response) : Response {

    if(empty($_ENV['cart']->order))  {
      return $this->response($response,[]);
    }

    return $this->response($response,Products::fullList(['id_store_orders' =>$_ENV['cart']->order['id']]));

  }

  /**
   * Ritorna lista di prodotti
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function save(Request $request, Response $response) : Response {


    if(empty($_ENV['cart']->order))  {
      throw new HttpBadRequestException($request,'cart_not_found');
    }

    $order = Orders::update($_ENV['cart']->order['id'],['status' => 'pending','oauth_tokens_jti' => null]);
   
    $cart = new Cart;

    $cart->create();

   $_ENV['cart'] = $cart;

    return $this->response($response,$order);

  }


}




?>