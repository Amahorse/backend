<?php

declare(strict_types=1);

namespace Elements\Tracking\Controllers;

use Elements\Tracking\Tracking;
use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class TrackingController extends Controller {

    public $messages = false;

    /**
     * Update the current tracking record.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpBadRequestException
     */
    public function updateCurrent(Request $request, Response $response) : Response
    {   
        // Check if the current tracking record exists
        if(!$tracking = Tracking::getCurrentId()) {
            throw new HttpBadRequestException($request,'tracking_not_found');
        }
      
        // Update the id_agents if it is set in the query parameters
        if(isset($this->data['ag'])) {
            $this->data['id_agents'] = $_GET['ag'];
        }
      
        // Call the update method of the parent class with the tracking id
        return $this->update($request,$response,['id' => $tracking['id']]);
    }
    
}

?>