<?php

declare(strict_types=1);

namespace Kodelines\Middleware;

use DateTime;
use Kodelines\Log;
use Kodelines\Helpers\Language;
use Kodelines\Helpers\Locale;
use Kodelines\Tools\Xss;
use Kodelines\Tools\Str;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use DI\Container;
use Slim\Psr7\Response;
use Throwable;

/**
 * Slim 4 Base path middleware.
 */
class ApiMiddleware implements MiddlewareInterface
{

    /**
     * Contine Xname per gli headers in lettura e scrittura 
     *
     * @var [type]
     */
    private $Xname;


    /**
     * Contiene dati richiesta
     */
    public $data;

    /**
     * PSR7 container
     *
     * @param ContainerInterface $container
     */
    public Container $container;



    public function __construct(Container $container) {

        $this->container = $container;

    }

    
    /**
     * Example middleware invokable class
     *
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {

        //Setto valori lingua e locale dagli header
        $this->Xname = config('api','xname');
       

        //Controllo se c'è locale su header, altrimenti faccio build pulito che prende il default
        if($localeHeader = $request->getHeaderLine($this->Xname."Locale")) {
            Locale::build($localeHeader);
        } 

        //Controllo se c'è lingua su header, altrimenti faccio build pulito che prende il default o l'automatico
        if($langHeader = $request->getHeaderLine($this->Xname."Language")) {    
            Language::build($langHeader);
        } else {
            //Nel contesto api pubbliche buildo sempre quella di default se non passato header, se sono api interne invece faccio i controlli normali in srssione
            Language::build(config('default','language'));      
        }

        if($request->getMethod() == 'GET' && !empty($_GET)) {
            $this->data = $_GET;
        } else {
            $this->data = (array)$request->getParsedBody();
        }

        //Assegno valori richiesta alla app cosi sono sempre accessibili nell'interfaccia controller senza che venga estesa da questa classe
        $this->container->set('requestData', $this->data);


        try {
     
            $response = $handler->handle($request);

        } catch (Throwable $exception) {
         
            return $this->error($exception);
        } 
        
        $content = (string)$response->getBody();

        $status = $response->getStatusCode();

        //Se lo status è redirect ritorno solo la response come è
        if($status == 302 || $status == 301) {
            return $response;
        }   
        
        //Altrimenti ritorno headers applicazione
        $response = $this->buildHeader()->withStatus($status);

        $response->getBody()->write($content);


        //TODO: mettere su db origin conosciute
        if(dev() && (!$request->getHeaderLine("Access-Control-Allow-Origin") || $request->getHeaderLine("Access-Control-Allow-Origin") !== '*')) {
           //$response = $response->withHeader('Access-Control-Allow-Origin', '*');
        }

        return $response;
    }

    /**
     * Definisce gli headers permessi dalla applicazione
     *
     * @return string
     */
    public function allowedHeaders(): string {  

        $headers= [
            $this->Xname."Language",
            $this->Xname."Domain",
            $this->Xname."Locale",
            $this->Xname."ClientType",
            $this->Xname."IdResellers",
            $this->Xname."IdStores",
            $this->Xname."IdAgents",
            $this->Xname."IdCountries",
            $this->Xname."IdCart"
        ];

        return implode(',',$headers);
    }

    /**
     * Costruisce header standard per risposte API
     *
     * @return Response
     */
    public function buildHeader(): Response {
 
        $response = new Response();

        //Set Headers
        $response = $response
        ->withHeader('Content-Type', 'application/json')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, ' . $this->allowedHeaders())
        ->withHeader('Access-Control-Expose-Headers', $this->allowedHeaders())
        ->withHeader('Access-Control-Allow-Methods', '*')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader("Cache-Control", "no-store")
        ->withHeader("Pragma", "no-cache");

        //TODO: accesso origini api
        if(empty($response->getHeaderLine("Access-Control-Allow-Origin"))) {
            //$response = $response->withHeader('Access-Control-Allow-Origin', '*');
        }

      
        //Write time on page debug when script is finished
        new Log("api",round((microtime(true) - _TIMESTART_), 4), ["path" => $_SERVER['REQUEST_URI']]);

        return $response;
    }

    /**
     * Custom error handler che scrive errore e ritorna stato e messaggio
     *
     * @param Throwable $exception
     * @return Response
     */
    public function error(Throwable $exception): Response {
    
        //Se codice errore è vuoto ritorna 500
        $code = $exception->getCode();

        if(empty($code)) {
            $code = 500;
        }

    

        $response = $this->buildHeader()->withStatus($code);

        $message = [  
            "status" => $code,
            "error" => $this->statusMessage($code),
            "message" => $exception->getMessage(),
            "path" => $_SERVER['REQUEST_URI']
        ];

        //Se in dev manda debug in risposta
        if(dev()) {

            $message["debug"] = [
                "data" => $this->data,
                "timestamp" => new DateTime(),
                "timing" => round((microtime(true) - _TIMESTART_), 4),
                "file" => $exception->getFile(),
                "line" =>  $exception->getLine()
            ];

        }

        //Non si loggano errore di validazione campi
        if(!$exception instanceof \Kodelines\Exception\ValidatorException)  {
            
            //Questi errori sono più gravi
            new Log("errors",round((microtime(true) - _TIMESTART_), 4), $message);

        } 


        
        //Pretty print only on dev
        if(!dev()) {
            $response->getBody()->write(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); 
        } else {
            $response->getBody()->write(json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        

        return $response;

    }

    /**
     * Fixa dati in base a nomenclatura db
     *
     * @param array $data
     * @return array
     */
    public function fixData(array $data): array {

        $data = Xss::clean($data);

        foreach($data as $key => $value) {

            //gli id sono sempre numerici
            if(($key == 'id' || Str::startsWith($key,'id_')) && is_string($value)) {
                $data[$key] = id($value);
            }

        }

        return $data;
    }


  /**
   * Ritorna stringa di testo in base a codice errore del server
   *
   * @param integer $code
   * @return string
   */
  public function statusMessage(int $code):string {

      $status = array(
          100 => 'Continue',
          101 => 'Switching Protocols',
          102 => 'User Protected',
          103 => 'Guest Protected',
          200 => 'OK',
          201 => 'Created',
          202 => 'Accepted',
          203 => 'Non-Authoritative Information',
          204 => 'No Content',
          205 => 'Reset Content',
          206 => 'Partial Content',
          300 => 'Multiple Choices',
          301 => 'Moved Permanently',
          302 => 'Found',  // 1.1
          303 => 'See Other',
          304 => 'Not Modified',
          305 => 'Use Proxy',
          307 => 'Temporary Redirect',
          400 => 'Bad Request',
          401 => 'Unauthorized',
          402 => 'Payment Required',
          403 => 'Forbidden',
          404 => 'Not Found',
          405 => 'Method Not Allowed',
          406 => 'Not Acceptable',
          407 => 'Proxy Authentication Required',
          408 => 'Request Timeout',
          409 => 'Conflict',
          410 => 'Gone',
          411 => 'Length Required',
          412 => 'Precondition Failed',
          413 => 'Request Entity Too Large',
          414 => 'Request-URI Too Long',
          415 => 'Unsupported Media Type',
          416 => 'Requested Range Not Satisfiable',
          417 => 'Expectation Failed',
          500 => 'Internal Server Error',
          501 => 'Not Implemented',
          502 => 'Bad Gateway',
          503 => 'Service Unavailable',
          504 => 'Gateway Timeout',
          505 => 'HTTP Version Not Supported',
          509 => 'Bandwidth Limit Exceeded'
      );

      return ($status[$code])?$status[$code]:$status[500];
  }

}

?>