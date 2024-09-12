<?php

declare(strict_types=1);

namespace Kodelines\Exception;

use Exception;
use Throwable;

class ValidatorException extends Exception
{

    /**
     * Campo che da errore
     *
     * @var string
     */
    public mixed $field;

    /**
     * 
     * 
     * @param ServerRequestInterface $request
     * @param string                 $message
     * @param int                    $code
     * @param Throwable|null         $previous
     */
    public function __construct(
        string $message = '',
        mixed $field = false,
        ?Throwable $previous = null
    ) {

        $this->code = 417;

        $this->field = $field;

        //TODO: se c'è field parsare messaggio invece di concatenare
        if($field) {
            $this->message = $field. '_' . $message;
        } else {
            $this->message = $message;
        }
    

    }

}

?>