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
      'username' =>  Users::generateUsername(),
      'pa' => 0
     ];

    //Valori definiti da app o auth middleware
    if(defined('_CLIENT_TYPE_')) {
      $this->defaults['type'] = _CLIENT_TYPE_;
    }

    if(defined('_ID_AGENTS_')) {
      $this->defaults['id_agents'] = _ID_AGENTS_;
    }

    if(defined('_ID_RESELLERS')) {
      $this->defaults['id_resellers'] = _ID_RESELLERS_;
    }

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
        "pec" => ["email"],
        "first_name" => ["required"],
        "last_name" => ["required"],
        "language" => ["required"],
        "auth" => ["required"]
    ];



    /**
     * Campi uploads
     *
     * @var array
     */
    public $uploads = ['image'];


  /**
   * Query principale
   *
   * @param array $filters
   * @return string
   */
  public function query($filters = []):string {

    $query = "
    SELECT
      users.*,";

      if(_CONTEXT_ == 'admin') {
        $query .= "
        tracking.ref AS tracking_ref,
        tracking.browser AS tracking_browser,
        tracking.os AS tracking_os,
        tracking.language AS tracking_language,
        tracking.cpg AS tracking_cpg,
        tracking.ip AS tracking_ip,
        ";
      }

      $query .= "
      countries.country,
      countries.iso AS country_short,
      countries_states.state,
      countries_states.state_short
      
    FROM users";

    if(_CONTEXT_ == 'admin') {
      $query .= " LEFT JOIN tracking ON users.id_tracking = tracking.id";
    }

    if(!empty($filters['token'])) {
      
      $query .= " JOIN oauth_tokens ON users.id = oauth_tokens.id_users AND oauth_tokens.access_token = " . encode($filters['token']);

      if(!empty($filters['client_id'])) {
        $query .= "AND oauth_tokens.client_id = " . encode($filters['client_id']);
      }

    }

    $query .= "
      LEFT JOIN countries ON users.id_countries = countries.id
      LEFT JOIN countries_states ON users.id_countries_states = countries_states.id
    WHERE users.id IS NOT NULL
      ";

  
      if(!empty($filters['auth_min']))  {
        $query .= " AND users.auth >= " . (int)$filters['auth_min'];
      }
  
      if(!empty($filters['auth_max']))  {
        $query .= " AND users.auth < " . (int)$filters['auth_max'];
      }
  
      $query.= $this->applyFilters($filters);
    

    return $query;

  }



  /**
   * Fixa i dati e mette variabili su validatore in base a tipo di utente caricato
   *
   * @param array $data
   * @return array
   */
  public function fix($data = []):array {

    //Per b2b sono richiesti altri campi quindi faccio push a validatore e questa funzione va chiamata sempre prima di chiamare funzione create del model
    if(!empty($data['type']) && $data['type'] !== 'b2c') {

      array_push($this->validator,[
        "vat_number" => ["required","unique"],
        "phone" => ["required"],
        "sdi_code" => ["required"],
        "business_name" => ["required"],
        "business_type" => ["required"]
      ]);

      if(empty($data['header'])) {
        $data['header'] = $data['business_name'];
      }
  
      
    }  else {

      if(empty($data['header']) && !empty($data['first_name']) && !empty($data['last_name'])) {
        $data['header'] = $data['first_name'] . ' ' . $data['last_name'];
      }

    }

    //Email è già presente dopo aver validato ma la metto minuscola prima di inserire nel database
    if(!empty($data['email'])) {
      $data['email'] = mb_strtolower($data['email']);
    }

    //TODO: questo non funziona, può essere un trigger sul db
    //Fixo dati del numero di telefono 
    if(!empty($data['id_countries']) && empty($data['phone_prefix']) && !empty($data['phone']) && $phone_prefix = Countries::getPhonePrefix($data['id_countries'])) {
      $data['phone_prefix'] = $phone_prefix;
    }

    return $data;
  }



}
