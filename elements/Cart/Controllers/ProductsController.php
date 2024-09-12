<?php

declare(strict_types=1);

namespace Elements\Cart\Controllers;

use Kodelines\App;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
use Slim\Exception\HttpBadRequestException;
use Kodelines\Abstract\Controller;
use Kodelines\Tools\File;
use Elements\Cart\Cart;
use Elements\Orders\Orders;
use Elements\Orders\Products;

class ProductsController extends Controller {

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
   * Fa controllo se prodotto esiste nel carrello esiste altrimenti fa throw exception
   *
   */
  private function productCheck(Request $request, int $id) {
    
    if(empty($_ENV['cart']->order) || !Products::checkOrder($_ENV['cart']->order['id'],$id)) {
      throw new HttpBadRequestException($request,'cart_error'); 
    }

  }


/**
   * Ritorna lista di oggetti dal model
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function add(Request $request, Response $response, $args) : Response {

    if(empty($_ENV['cart']->order)) {
     $_ENV['cart']->create();
    }

    $product = Products::add($this->data,$_ENV['cart']->order,false);

    //TODO: questo è completamente da rimuovere perchè i componenti vanno aggiunti successivamente e separatamente 
    //Componenti aggiunti custom utente, avendo settato già prima il components se è un pacchetto, vengono uniti dalla funzione addComponents
    if(!empty($this->data['components'])) { 

      //Fix per componenti perchè follow price e follow quantity per componenti custom deve essere a 0
      //TODO: questa cosa si potrebbe mettere customizzata per tipo prodotto
      foreach($this->data['components'] as $component) {
          
          //TODO: queste possono essere messe in visualizzazione front end o impostate a mano
          $component['follow_quantity'] = 1;
          
          $component['add_price'] = 1;

          $component['fixed_component'] = 0;

          //TODO: fix provvisorio per massimo 
          if(isset($component['id'])) {
              unset($component['id']);
          }

          $product['components'][] = Products::addComponent($product, $component,$_ENV['cart']->order);
      }


    }

    Orders::refresh($_ENV['cart']->order);
    
    return $this->response($response,$product);
  }

  /**
   * Ritorna lista di oggetti dal model
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function get(Request $request, Response $response, $args) : Response {

    if(empty($_ENV['cart']->order)) {
      return $this->response($response,[]);
    }

    $this->productCheck($request,id($args['id']));

    $data = Products::fullGet(id($args['id']),['id_store_orders' =>$_ENV['cart']->order['id']]);

    return $this->response($response,$data);

  }

  /**
   * Ritorna lista di oggetti dal model
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function edit(Request $request, Response $response, $args) : Response {

    $this->productCheck($request,id($args['id']));

    //TODO: come quello sopra, mettere questo in Products::add e fare vari controlli per esistenza file precedente
    if(!empty($this->data['preview']) && !File::isImage($this->data['preview'])) {

      $uploaded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->data['preview']));

      $file = uniqid('cart-') . '.png';

      if(file_put_contents(_DIR_UPLOADS_  . 'carts/' . $file,$uploaded)) {
        $this->data['preview'] = $file;
      }

    }


    Products::edit(id($args['id']),$this->data,$_ENV['cart']->order);

    Orders::refresh($_ENV['cart']->order);
    
    return $this->response($response,Products::fullGet(id($args['id'])));

  }


  /**
   * Ritorna lista di oggetti dal model
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response, $args) : Response {

    if(empty($_ENV['cart']->order)) {
      throw new HttpBadRequestException($request,'cart_not_found'); 
    }

    $this->productCheck($request,id($args['id']));

    $data = Products::remove(id($args['id']),$_ENV['cart']->order);

    Orders::refresh($_ENV['cart']->order);
    
    return $this->response($response,$data);

  }


  /**
   * Aggiunge componenti a prodotto in ordine
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function addComponent(Request $request, Response $response, $args) : Response {

    if(empty($_ENV['cart']->order)) {
      throw new HttpBadRequestException($request,'cart_not_found'); 
    }

    $this->productCheck($request,id($args['id']));

    $product = Products::fullGet(id($args['id']));
    
    Products::addComponent($product,$this->data,$_ENV['cart']->order);

    Orders::refresh($_ENV['cart']->order);

    $data = Products::fullGet($product['id']);

    return $this->response($response,$data);

  }


}