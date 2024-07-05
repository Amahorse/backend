<?php

declare(strict_types=1);

namespace Elements\Categories\Controllers;

use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpInternalServerErrorException;

class CategoriesController extends Controller {

    public $defaultFilters = [
        'status' => 'published'
    ];

    /**
     * Ritorna lista categorie principali con sottocategorie
     */
    public function main(Request $request, Response $response) : Response {

      $filters = ['main' => 1];

      if(defined('_CLIENT_TYPE_')) {
        $filters['client_type'] = _CLIENT_TYPE_;
      } 

      //TODO: mettere in cache con istanza container
      if(!$list = $this->model->list(array_merge($this->data,$filters))) {
        throw new HttpInternalServerErrorException($request);
      }

      $categories = [];

      foreach($list as $category) {

          $category['subcategories'] = $this->model->list(array_merge($this->data,['id_categories_main' => $category['id'],'main' => 0]));

          $categories[] = $category;
      }
  
  
      return $this->response($response,$categories);
  
    }

}

?>