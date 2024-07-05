<?php

/**
 * Classe speciale che può essere istanziata solo una volta in tutto il sistema ad ogni api call, 
 * funge da decorator api per i model, se non dichiarati e sovrascritti nei controller di ogni elemento
 * crea delle chiamate api standard ai modelli che a loro volta possono sovracrivere i metodi standard 
 * dell'interfaccia modello, questo automatizza tutto il processo di sviluppo API
 */

declare(strict_types=1);

namespace Kodelines\Abstract;

use Kodelines\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Kodelines\Interfaces\ControllerInterface;
use Kodelines\Interfaces\ModelInterface;
use Kodelines\Tools\Str;
use Kodelines\Tools\Json;
use Kodelines\Tools\Query;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe abstract perchè deve essere estensione dei controller che gli aggiungono questi metodi
 */

abstract class Controller implements ControllerInterface
{

  /**
   * Contiene dati della richiesta 
   *
   * @var [type]
   */
  public $data;

  /**
   *  Contiene i files caricati in upload
   */
  public $uploads;


  /**
   * Se il model è dichiarato dal controller dell'elemento, il costruttore lo istanzia e vengono forniti i metodi del model tramite 
   * modello standard, deve essere un oggetto che estende la classe model
   *
   * @var ModelInterface
   */
  public ModelInterface $model;

  /**
   * Se settata true i valori enum o set delle api vengono tradotti e gli array convertiti come oggetti name -> value
   * Può essere impostato come globale su configurazioni su tutto il controller o su singola funzione prima del return
   * La conversione viene fatta alla response su funzione parse
   *
   * @var bool
   */
  public $translate = false;



  /**
   * Se false disabilita messaggi di notifica nella risposta json per il controller
   */
  public $messages = true;


  /**
   * Contiene i valori da non mostrare nelle risposte api
   *
   * @var array
   */
  public $hidden = [];


  /**
    * Pre filtri che vengono applicate a tutte le query di lista, non possono essere sovrascritti da quelli passati in get
    *
    * @var array
    */
  public $defaultFilters = [];

  /**
   * Traduzioni per API
   *
   * @var array
   */
  public $translations = [];


  /**
   * PSR7 container
   *
   * @param ContainerInterface $container
   */
  public ContainerInterface $container;

  /**
   * Nome classe per trovare il Model corrispondente al Controller
   *
   * @var string
   */
  public $className;

  /**
   * La richiesta viene processata dal container e dalla app principale nel middleware che ritorna dati fixati
   * e nell'array $this->data per risparmiare tutte le volte di recuperarli nel metodo PSR7 con le varie funzioni
   *
   * @param ContainerInterface $container
   */
  public function __construct(ContainerInterface $container) {

    $this->container = $container;

    $this->data = App::getInstance()->requestData;

    $this->translations = App::getInstance()->translations;
  
    //Se non è vuoto ma dichiarato in classe come stringa, la variabile modello diventa un oggetto di quella classe
    if(!empty($this->model)) {
      $this->model = new $this->model;
    } else {
    
      //Cerca un model con lo stesso nome nella cartella parallela, se non lo trova, il controller è comunque chiamabile ma con metodi non RestFul standard
      if($model = $this->findModel(get_called_class())) {
 
        $this->model = new $model;
      }
    
    }

    //TODO: questo e rimuovi hidden va fatto in base a $request xhr  
    $this->translate = config('api','translate');
    


  }

  /**
   * Trova un modello corrispondente al controller nella cartella parallela elemento
   *
   * @param string $class
   * @return boolean|string
   */
  protected function findModel(string $class):bool|string {


    $classParts = explode('\\', $class);

    $className = str_replace('Controller','',end($classParts));

    $counter = count($classParts) -1;

    $class = '';

    foreach($classParts as $key => $part) {

      if($key == $counter) {
        break;
      }

      if($part == 'Controllers') {
        continue;
      }

      $class .= '\\' . $part;

    }

    $class .= '\Models\\' . $className . 'Model'; 

    if(!class_exists($class)) {
      return false;
    }

    $this->className = $className;

    return $class;

  }


  /**
   * Funge da fix per la risposta base, formatta il json e mette eventuali risposte di redirect
   *
   * @param Response $response
   * @param array $data
   * @return Response
   */
  public function response(Response $response, mixed $data = false): Response {

    if(is_bool($data)) {
      $data = [];
    }

    if(is_string($data)) {
      $data = [$data];
    }


    $response->getBody()->write(Json::encode($data));

    return $response->withStatus(200);

  }

    /**
   * Funge da fix per la risposta base, formatta il json e mette eventuali risposte di redirect
   *
   * @param Response $response
   * @param array $data
   * @return Response
   */
  public function redirect(Response $response, string $uri, $data = []): Response {

    return $response->withHeader('Location',Query::build($uri,$data))->withStatus(302);

  }


  /**
   * Ritorna lista di oggetti dal model
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function list(Request $request, Response $response) : Response {

    if($request->getMethod() !== 'GET') {
      throw new HttpMethodNotAllowedException($request);
    }

    if(empty($this->model)) {
      throw new HttpNotFoundException($request);
    }

    $data = [];

    foreach($this->model->list(array_merge($this->data,$this->defaultFilters)) as $row) {
      $data[] = $this->parse($row);
    }

    return $this->response($response,$data);

  }

  /**
   * Ritorna elemento o not found per id numerico
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function get(Request $request, Response $response,array $args) : Response {

    if($request->getMethod() !== 'GET') {
      throw new HttpMethodNotAllowedException($request);
    }

    if(empty($this->model)) {
      throw new HttpNotFoundException($request);
    }

    if(empty($args['id'])) {
      throw new HttpNotFoundException($request);
    }

    if(!$data = $this->model->get(id($args['id']),$this->data)) {
      throw new HttpNotFoundException($request);
    }
    
    $data = $this->parse($data); 

    return $this->response($response,$data);

  }

  /**
   * Ritorna elemento o not found per slug
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function slug(Request $request, Response $response,array $args) : Response {

    if($request->getMethod() !== 'GET') {
      throw new HttpMethodNotAllowedException($request);
    }

    if(empty($this->model)) {
      throw new HttpNotFoundException($request);
    }

    if(empty($args['slug'])) {
      throw new HttpNotFoundException($request);
    }

    if(!$data = $this->model->slug($args['slug'],$this->data)) {
      throw new HttpNotFoundException($request);
    }
    
    $data = $this->parse($data); 

    return $this->response($response,$data);

  }

  /**
   * Crea un elemento
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function create(Request $request, Response $response) : Response {

    if($request->getMethod() !== 'POST') {
      throw new HttpMethodNotAllowedException($request);
    }

    if(empty($this->model)) {
      throw new HttpNotFoundException($request);
    }

    $object = $this->model->create($this->data);

    

    return $this->response($response,[mb_strtolower($this->className) => $this->parse($object)]);

  }


  /**
   * Aggiorna elemento da id
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function update(Request $request, Response $response,array $args) : Response{

    if($request->getMethod() !== 'PUT') {
      throw new HttpMethodNotAllowedException($request);
    }

    if(empty($this->model)) {
      throw new HttpNotFoundException($request);
    }

    if(empty($args['id'])) {
      throw new HttpNotFoundException($request);
    }

    if(!$this->model->get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

    $object = $this->model->update(id($args['id']),$this->data);

    

    return $this->response($response,[mb_strtolower($this->className) => $this->parse($object)]);


  }

  /**
   * Elimina elemento da id
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function delete(Request $request, Response $response,array $args) : Response{

    if($request->getMethod() !== 'DELETE') {
      throw new HttpMethodNotAllowedException($request);
    }
 
    if(empty($this->model)) { 
      throw new HttpNotFoundException($request);
    }

    if(empty($args['id'])) {
      throw new HttpNotFoundException($request);
    }

    if(!$this->model->get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

    $this->model->delete(id($args['id']));

    return $this->response($response,true);


  }

  /**
   * Filtra e traduce se necessario i campi di una chiamata
   *
   * @method parse
   * @param  array $data campi del prodotto
   * @return array  Array di campi tradotti e filtrati
   */
  public function parse(mixed $data):array {

    if(!is_array($data)) {
	    return [];
    }

    if(!empty($this->hidden)) {
      $data = $this->removeHidden($data);
    }



    //Faccio foreach per campi nascosti
    foreach($data as $key => $value) {

      //In modalità sviluppo fa vedere tutti i campi in ogni caso

      //TODO: questo andrebbe fatto solo per front end (scope non admin)

      if(!dev()) {

        if(in_array($key,$this->hidden)) {

          unset($data[$key]);
  
          continue;
  
        }  
      }
   
      //Parso anche sub array
      if(is_array($data[$key])) {
        $data[$key] = $this->parse($data[$key]);
      }

    }

    ksort($data);

    return $data;

  }


  /**
   * Rimuove campi hidden darisposta json
   *
   * @param array $data
   * @return array
   */
  public function removeHidden(array $data): array {

    foreach($this->hidden as $hidden) {
      if(array_key_exists($hidden,$data)) {
        unset($data[$hidden]);
      }
    }

    return $data;

  } 



}
