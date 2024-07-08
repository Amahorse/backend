<?php
declare(strict_types=1);

namespace Elements\Orders\Controllers;

use Elements\Orders\Stats;
use Elements\Orders\Orders;
use Elements\Orders\Products;
use Kodelines\Abstract\Controller;
use Slim\Exception\HttpNotFoundException;
use Kodelines\Exception\ValidatorException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class OrdersController extends Controller {


  /**
   * Ritorna lista di oggetti con statistiche
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function fullList(Request $request, Response $response) : Response {

    if(isset($this->data['id_stores']) && $this->data['id_stores'] === 0) {
      $this->data['id_stores'] = null;
    } elseif (isset($this->data['id_stores']) && $this->data['id_stores'] == '') {
      unset($this->data['id_stores']);
    }

    $orders = Orders::list($this->data);

    return $this->response($response,[
        'orders' => $orders,
        'stats' => Stats::sum($orders)
    ]);

  }

  
  /**
   * L'update fa prima il controllo del vecchio stato e del nuovo
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function update(Request $request, Response $response, $args) : Response {

    if(!$order = $this->model->get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

   
    if(!empty($this->data['status'])) {

      if($this->data['status'] == 'confirmed' && $order['status'] <> 'confirmed') {

        Orders::confirm(id($args['id']),isset($this->data['send_email_to_customer']));
  
      }

      if($this->data['status'] == 'deleted' && $order['status'] == 'confirmed') {
        Orders::cancel(id($args['id']));
      }

     
    }


    return $this->response($response, $this->model->update(id($args['id']),$this->data));

  }



  /**
   * Gli ordini non possono essere mai cancellati ma solo annullati
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response, $args) : Response {

    return $this->response($response,Orders::cancel(id($args['id'])));

  }


  /**
   * Conferma ordine via api
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function confirm(Request $request, Response $response, $args) : Response {

    return $this->response($response,Orders::confirm(id($args['id'])));

  }



  /**
   * Esporta gli ordini
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function export(Request $request, Response $response, $args) : Response {

    return $this->response($response,Orders::export($args['type']));

  }

/**
   * Esporta gli ordini
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function invoice(Request $request, Response $response, $args) : Response {

    if(!$order = $this->model->get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

    if(!Orders::invoice($order,$this->data,$args['type'])) {
      throw new ValidatorException('invoice_creation_error');
    }

    return $this->response($response,true);

  }


  /**
   * Esporta gli ordini
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function push(Request $request, Response $response) : Response {

    $data = [];

    

    if(empty($this->data['order']) || !is_array($this->data['order'])) {
      throw new ValidatorException('order_is_empty');
    }

    if(empty($this->data['products']) || !is_array($this->data['products'])) {
      throw new ValidatorException('products_not_found');
    }

    if(!$data['order'] = $this->model->create($this->data['order'])) {
      throw new ValidatorException('order_creation_failed');
    }

    foreach($this->data['products'] as $product) {
      if(!$data['products'][] = Products::add($product,$data['order'],false)) {
        throw new ValidatorException('Error on product: ' . $product['id']);
      }
    }

    return $this->response($response,$data);
  }


}
