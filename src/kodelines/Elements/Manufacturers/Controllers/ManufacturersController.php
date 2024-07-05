<?php
declare(strict_types=1);

namespace Elements\Manufacturers\Controllers;

use Elements\Manufacturers\Manufacturers;
use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;

class ManufacturersController extends Controller {

  public $hidden = [
    "bank_account",
    "bank_name",
    "bic",
    "file_agreement",
    "fiscal_code",
    "iban",
    "id_users",
    "pa",
    "pec",
    "sdi_code",
    "swift",
    "vat_number",
    "contacts"
  ];


    /**
     * Get a manugacturer by slug with image gallery
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
  public function images(Request $request, Response $response, $args) : Response {

    if(!empty($args['slug']) && !$manufactuer = Manufacturers::slug($args['slug'])) {
      throw new HttpNotFoundException($request);
    }

    if(!empty($args['id']) && !$manufactuer = Manufacturers::get($args['id'])) {
      throw new HttpNotFoundException($request);
    }

    $images = Manufacturers::getImages($manufactuer['id']);

    return $this->response($response,$images);

  }


}
