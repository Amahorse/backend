<?php
declare(strict_types=1);

namespace Elements\Orders\Controllers;

use Kodelines\Db;
use Kodelines\Interfaces\ModelInterface;
use Kodelines\Abstract\Controller;
use Slim\Exception\HttpNotFoundException;
use Kodelines\Exception\ValidatorException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Kodelines\Tools\File;
use Elements\Labels\Labels;


class ProductsController extends Controller {


  public ModelInterface $model;


  /**
   * Ritorna lista di oggetti dal model
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function fullList(Request $request, Response $response) : Response {


    if(empty($this->model)) {
      throw new HttpNotFoundException($request);
    }

    $data = [];

    foreach($this->model->fullList($this->data) as $row) {
      $data[] = $this->parse($row);
    }

    return $this->response($response,$data);

  }

  /**
   * Ritorna elemento o not found
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function fullGet(Request $request, Response $response,array $args) : Response {


    if(empty($this->model)) {
      throw new HttpNotFoundException($request);
    }

    if(empty($args['id'])) {
      throw new HttpNotFoundException($request);
    }

    if(!$data = $this->model->fullGet(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }
    
    $data = $this->parse($data); 

    return $this->response($response,$data);

  }


  /**
   * Update prodotto ordine è chiamato dal backoffice e basta perchè sovrascrive direttamente file etichette, per il front end e il configuratore ci sono funzioni carrello
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function update(Request $request, Response $response, $args) : Response {

    if(!$product = $this->model->get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

    if(!empty($product['label_preview'])) {
      $filename = File::name($product['label_preview']);
    } else {
      $filename = uniqid("custom-");
    }

    if(!empty($product['front_cut']) && $product['front_cut'] == 'rounded') {
      $file = $filename.'.png';
    } else {
      $file = $filename.'.jpg';
    }

    //Setto array per update label 
    $label = [];

    //Questo è un aggiornamento etichetta forzata 
    if(!empty($this->data['label_preview']) && $this->data['label_preview'] <> $product['label_preview']) {

      $uploaded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->data['label_preview']));

      if(!file_put_contents(uploads() .  'custom/images/' . $file,$uploaded)) {
        throw new ValidatorException('image_upload_failed');
      }
  
      $label['preview'] = $file;
    }

    //Questo è un aggiornamento etichetta forzata 
    if(!empty($this->data['label_file']) && $this->data['label_file'] <> $product['label_file']) {

      if(!$ext = File::mime2ext($this->data['label_file'])) {
        throw new ValidatorException('file_not_valid');
      } 

      $file = $filename . '.' . $ext;

      //TODO: questo dovrebbe essere gestito da uploads
      if($ext == 'pdf') {
        $uploaded = base64_decode(preg_replace('#^data:application/\w+;base64,#i', '', $this->data['label_file']));
      } else {
        $uploaded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->data['label_file']));
      }


      if(!file_put_contents(uploads() .  'custom/ready/' . $file,$uploaded)) {
        throw new ValidatorException('file_upload_failed');
      }
  
      $label['file'] = $file;

    }

     //Questo è un aggiornamento etichetta forzata 
     if(!empty($this->data['label_settings']) && $this->data['label_settings'] <> $product['label_settings']) {


      $file = $filename . '.json';

      if(file_put_contents(uploads()  . 'custom/json/' . $file,$this->data['label_settings'])) {
        $label['settings'] = $file;
      }

  
    }


    if(!empty($label)) { 
      
      if(!empty($product['id_labels']) || !$this->model->get(id($product['id_labels']))) {

        $label['type'] = 'custom';

        $label['id_users'] = $product['id_users'];

        $label = Labels::create($label);

      } else {
        $label = Labels::update(id($product['id_labels']),$label);
      }

      $this->data['id_labels'] = $label['id'];
      
    }

    

    return $this->response($response,$this->model->update(id($args['id']),$this->data));

  }

  /**
   * Upload nuova preview prodotto
   *
   * //NOTA: Funzione sperimentale che genera solo file di preview da editor pixie
   * 
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @return Response
   */
  public function preview(Request $request, Response $response, $args = []) : Response {

    if(!$product = $this->model->get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

    //Questo è un aggiornamento etichetta forzata 
    if(!empty($this->data['preview'])) {

      if(!empty($product['preview'])) {
        $file = $product['preview'];
      } else {
        $file = uniqid("custom-") . '.jpg';
      }

      $uploaded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->data['preview']));

      if(!file_put_contents(uploads() .  'carts/' . $file,$uploaded)) {
        throw new ValidatorException('image_upload_failed');
      }

      $this->data['preview'] = $file;

      return $this->response($response,$this->model->update(id($args['id']),$this->data));

    }

    return $this->response($response,false);

  }



  /**
   * Aggiornamento lotto prodotto
   *
   * Andrebbe messo su childs del  model ma no perchè questo va aggiornato separatamente e opzionalmente da pannello 
   * 
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @return Response
   */
  public function lotto(Request $request, Response $response, $args = []) : Response {

    if(!$product = $this->model->get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

    //Questo è un aggiornamento etichetta forzata 
    if(isset($this->data['lotto_'.$args['id']])) {
      Db::insertMultiple($this->data['lotto_'.$args['id']],'store_orders_products_lotto', 'id_store_orders_products', $args['id'],Db::getArray("SELECT * FROM store_orders_products_lotto WHERE id_store_orders_products = " .$args['id']));
    }

    return $this->response($response,$this->data);

  }



}
