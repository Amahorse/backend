<?php

declare(strict_types=1);

namespace Elements\Users\Models;

use Kodelines\Oauth\Scope;
use Kodelines\Abstract\Model;
use Elements\Users\Users;
use Kodelines\Helpers\Countries;

class UsersModel extends Model
{

  /**
   * Setto default con le impostazioni dal constructor
   */
  public function __construct()
  {
    parent::__construct();

    $this->defaults = [
      'id_countries' => config('default','id_countries'),
      'type' => 'b2c',
      'language' => config('default','language'),
			'auth' => Scope::code('user'),
      'username' =>  Users::generateUsername()
     ];


    $this->defaults = $this->fix($this->defaults);

  }

  /**
   * Table for basequery
   * 
   * @type string
   */
  public $table = 'users';

    /**
   * The document folder inside 'uploads' dir
   *
   * @type string
   */
  public $documents = 'users';

  /**
   * Campi validatore
   *
   * @var array
   */
    public $validator = [
        "username" => ["required","unique"],
        "email" => ["required","email","unique"],
        "first_name" => ["required"],
        "last_name" => ["required"],
        "language" => ["required"],
        "auth" => ["required"]
    ];






  /**
   * Fixa i dati e mette variabili su validatore in base a tipo di utente caricato
   *
   * @param array $data
   * @return array
   */
  public function fix($data = []):array {

    //Email è già presente dopo aver validato ma la metto minuscola prima di inserire nel database
    if(!empty($data['email'])) {
      $data['email'] = mb_strtolower($data['email']);
    }


    return $data;
  }



}
