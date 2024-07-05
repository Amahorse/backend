<?php
declare(strict_types=1);

namespace Elements\Data\Controllers;

use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Data\Data;

class DataController extends Controller {

    /**
     * Questa funziona che se ha come argomento id utente ritorna quello di un utente specifico ma deve avere come autorizzazione administrator all'endpointm altrimenti è quello corrente ed è chiamata /profile/data
     */
    public function user(Request $request, Response $response,array $args) : Response {
      
        if(!$data = Data::list(['table_name' => 'users', 'table_id' => id($args['id_users'])])) {
            $data = [];
        }

        return $this->response($response,$data);
    
      }

}
