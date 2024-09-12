<?php

declare(strict_types=1);

namespace Kodelines\Exception;

use Exception;
use Throwable;
use Kodelines\App;
use Kodelines\Log;

class RuntimeException extends Exception
{

    /**
     * 
     * La runtime exception muore solo se in modalità sviluppo e scrive opzionalmente log, sono errori che non influiscono pesantemente sul sistema
     * 
     * @param ServerRequestInterface $request
     * @param string                 $message
     * @param int                    $code
     * @param Throwable|null         $previous
     */
    public function __construct(
        string $message = '',
        int $code = 500,
        ?Throwable $previous = null
    ) {

        $message .= " in " . $this->getFile() . " on line: " . $this->getLine();
 
           //Se app non inizializzata muore con il messaggio
        if(!App::checkInstance()) {
            die($message);
        }

        new Log("errors", $message);

        if (dev()) {
            die($message);
        }
    }
}
