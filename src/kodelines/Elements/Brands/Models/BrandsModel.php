<?php

declare(strict_types=1);

namespace Elements\Brands\Models;

use Kodelines\Abstract\Model;
use Kodelines\Oauth\Scope;

class BrandsModel extends Model {

  public $table = 'brands';
  
  public $documents = 'brands';

  public $uploads = ['cover','logo_png','logo_svg'];

 
}

?>