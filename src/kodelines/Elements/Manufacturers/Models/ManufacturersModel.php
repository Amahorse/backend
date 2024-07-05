<?php

declare(strict_types=1);

namespace Elements\Manufacturers\Models;

use Kodelines\Abstract\Model;

class ManufacturersModel extends Model  {

  public $table = 'manufacturers';

  /**
   * The document folder inside 'uploads' dir
   *
   * @type string
   */
  public $documents = 'manufacturers';




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
      "id_countries" => config('default','id_countries'),
      "type" => "other",
      "status" => 0
    ];

  }


  /**
   * Campi validatore
   *
   * @var array
   */
  public $validator = [
    "name" => ["required","unique"],
    "email" => ["required","email"],
    "pec" => ["email"],
    "phone_number" => ["phone"],
    "zip_code" => ["zip_code"]
  ];

  /**
   * Images to Upload
   *
   * @var array
   */
  public $uploads = ["logo","cover","file_agreement"];

  
  /**
   * Child tables
   *
   * @var array
   */
  public $childs = ["contacts"];

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
            manufacturers.*,
            manufacturers_lang.*,
            countries.country,
            countries.iso AS country_short,
            countries_states.state,
            countries_states.state_short,
            countries_regions.region
        FROM manufacturers 
            LEFT JOIN manufacturers_lang ON manufacturers_lang.id_manufacturers = manufacturers.id AND manufacturers_lang.language = ".encode($language)."
            LEFT JOIN countries ON manufacturers.id_countries = countries.id
            LEFT JOIN countries_states ON manufacturers.id_countries_states = countries_states.id
            LEFT JOIN countries_regions ON manufacturers.id_countries_regions = countries_regions.id
        WHERE manufacturers.id IS NOT NULL
        ";


        $query.= $this->applyFilters($filters);


    return $query;

  }



}

?>