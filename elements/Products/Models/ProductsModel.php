<?php

declare(strict_types=1);

namespace Elements\Products\Models;

use Kodelines\Abstract\Model;

class ProductsModel extends Model {

  public $documents = 'products';

  public $table = 'products';
  
  public $validator = [];

  public $uploads = ['cover'];


}

?>