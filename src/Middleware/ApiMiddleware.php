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

        //Build locale header
        define('_APP_LOCALE_',Locale::build($request->getHeaderLine("X-Locale")));

        //Build lingua header
        define('_APP_LANGUAGE_',Language::build($request->getHeaderLine("X-Language")));

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
            "X-Language",
            "X-Domain",
            "X-Locale",
            "X-ClientType",
            "X-IdResellers",
            "X-IdStores",
            "X-IdAgents",
            "X-IdCountries",
            "X-IdCart"
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
            $response->getBody()->write(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)); 
        } else {
            $response->getBody()->write(json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
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




}

?>