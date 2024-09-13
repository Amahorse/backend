<?php

declare(strict_types=1);

namespace Elements\Store\Controllers;

use Kodelines\Abstract\Controller;

class B2bController extends Controller {

  use \Elements\Store\Traits\StoreTrait;

  public $hidden = [];

  public $defaultFilters = [
    'available_b2b' => 1,
    'status' => 'on_sale'
  ];





}

?>