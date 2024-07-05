<?php

declare(strict_types=1);

namespace Elements\Contacts\Models;

use Kodelines\Abstract\Model;
use Elements\Tracking\Tracking;

class RequestsModel extends Model  {

  public $table = 'contacts_requests';


  /**
   * Setto default con le impostazioni dal constructor
   */
  public function __construct()
  {
    parent::__construct();

    /**
     * Campi predefiniti
     */
    $this->defaults = [
      "category" => 'contact',
      "status" => 'new',
      "type" => 'b2c',
      "language" => language(),
      "id_users" => user('id'),
      "id_countries" => config('default','id_countries'),
      "id_tracking" => Tracking::getCurrentId()
    ];


  }


   /**
    * Form inputs required
    *
    * @type array
    */
  public  $validator = [
    'email' => ['required','email']
  ];


  public function query($filters = []):string {

    $query = "
    SELECT
      contacts_requests.*,";

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
      users.username
    FROM contacts_requests ";

    if(_CONTEXT_ == 'admin') {
      $query .= "LEFT JOIN tracking ON contacts_requests.id_tracking = tracking.id";
    }

    $query .= " 
    	LEFT JOIN users ON users.id = contacts_requests.id_users
    	LEFT JOIN store_orders o ON o.id = contacts_requests.id_store_orders
    WHERE contacts_requests.id IS NOT NULL
    ";

    $query.= $this->applyFilters($filters);

    return $query;

  }



}

?>