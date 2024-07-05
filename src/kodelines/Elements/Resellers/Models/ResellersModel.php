<?php

declare(strict_types=1);

namespace Elements\Resellers\Models;

use Kodelines\Oauth\Scope;
use Kodelines\Abstract\Model;

class ResellersModel extends Model {

  public $table = 'resellers';

  public $documents = 'resellers';

  public $uploads = ["logo","cover"];

  public $childs = ['recharges','fees','shipping_adjustment'];

  public $validator = [
    'id_users' => ['required','unique'],
    'email_contacts' => ['required','email'],
    'email_orders' => ['required','email']
  ];

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
      "active" => 0,
      "status" => "draft"
    ];

  }



  /**
   * Query principale
   *
   * @param array $filters
   * @return string
   */
  public function query($filters = []):string {

    if (!empty($filters['lang'])) {
      $language = $filters['lang'];
    } else {
      $language = language();
    }

    $query = "
    SELECT
        resellers.*,
        resellers_lang.*,
        users.first_name,
        users.last_name,
        users.email,
        users.phone,
        users.business_name
      FROM resellers 
          LEFT JOIN users ON resellers.id_users = users.id AND users.auth >= ".Scope::code('reseller') . " 
          LEFT JOIN resellers_lang ON resellers_lang.id_resellers = resellers.id AND resellers_lang.language = ".encode($language)."
      WHERE resellers.id IS NOT NULL
    ";

    $query.= $this->applyFilters($filters);

    return $query;

  }





}

?>