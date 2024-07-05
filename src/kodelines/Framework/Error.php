<?php

declare(strict_types=1);

namespace Kodelines;

class Error extends \Exception
{

    public function __construct(string $error = 'Internal Server Error', $status = 500)
    {
        //TODO: psr logger con handler 
        //TODO: dev restituisce errore completo, prod restituisce solo messaggio
        
   		   //Return JSON response with error message
           http_response_code($status);

           header('Content-Type: application/json; charset=utf-8');

           $payload = ['error' => $error];

           new Log("errors", $error);

           echo json_encode($payload, JSON_PRETTY_PRINT);

           exit;
    }
}       

?>