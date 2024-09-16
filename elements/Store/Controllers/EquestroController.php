<?php

declare(strict_types=1);

namespace Elements\Store\Controllers;

use Kodelines\Abstract\Controller;

class EquestroController extends Controller {

  use \Elements\Store\Traits\StoreTrait;

  public $hidden = [];

  public $defaultFilters = [
    'available_b2c' => 1,
    'status' => ['on_sale','low_stock'],
    'brand_code' => 'ET'
  ];





}

?>