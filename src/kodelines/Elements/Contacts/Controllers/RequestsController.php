<?php

/**
 *
 * This file contains the RequestsController class, which is responsible for handling contact requests.
 *
 * @package Elements\Contacts\Controllers
 */

namespace Elements\Contacts\Controllers;

use Kodelines\Abstract\Controller;
use Kodelines\Mailer;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Kodelines\Exception\ValidatorException;
use Elements\Resellers\Resellers;
use Elements\Contacts\Requests;

class RequestsController extends Controller {

    /**
     * Handles the creation of a contact request.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @return Response The updated HTTP response object.
     * @throws ValidatorException If the privacy is not set or the data fails security checks.
     */
    public function create(Request $request, Response $response) : Response
    {   
        // Check if privacy is set for non-admin users
        if(_CONTEXT_ !== 'admin' && !isset($this->data['privacy'])) {
            throw new ValidatorException('privacy_not_set');
        }   

        // Check if the data passes security checks
        if(!Requests::checkData($this->data)) {
            throw new ValidatorException('security_block');
        }

        // Insert user from model
        $contact = $this->model->create($this->data);  
        
        // Subscribe to newsletter if requested
        if(isset($this->data['newsletter']) && !empty($this->data['email'])) {
            \Elements\Newsletter\Subscriptions::subscribe($this->data['email']);
        }
        
        // Send contact request email to reseller if applicable
        if(!empty($contact['id_resellers']) && $reseller = Resellers::get($contact['id_resellers'])) {
            if(!empty($reseller['email_contacts'])) {
                Mailer::queue('contact-request', $reseller['email_contacts'], $contact, $contact['language'],2);
            }
        } else {
            // Send contact request email to default contacts email
            if(!empty(config('contacts','email'))) {
                Mailer::queue('contact-request', config('contacts','email'), $contact, $contact['language'],2);
            }
        }
        
        return $this->response($response,$contact);
    }

}

?>