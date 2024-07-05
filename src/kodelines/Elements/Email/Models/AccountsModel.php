<?php

declare(strict_types=1);

namespace Elements\Email\Models;

use Kodelines\Abstract\Model;

class AccountsModel extends Model {

  public $table = 'email_accounts';

  public $validator = [
    'address' => ['required','email'],
    'username' => ['required'],
    'sender' => ['required'],
    'host' => ['required'],
    'port' => ['required','numeric'],
    'password' => ['required'],
    'secure' => ['required'],
  ];

  public $defaults = [
    'port' => 443,
    'secure' => 'tls'
  ];


}

?>