<?php

declare(strict_types=1);

namespace Elements\Products\Models;

use Kodelines\Abstract\Model;
use Elements\Products\Helpers\Type;

class ProductsModel extends Model {

  public $documents = 'products';

  public $table = 'products';
  
  public $validator = ['name' => ['required']];

  public $uploads = ['cover','retro'];

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
      'id_countries' => config('default','id_countries'),
      'type' => 'food'
    ];


  }


   /**
   * Questa è una funzione diversa dalle altre query in quanto prende tabelle dinamicamente in base a paramentri e non ha filtri predefiniti
   *
   * @param array $filters
   * @return string
   */
  public function query(array $filters = []):string 
  {

    $language = !empty($filters['language']) ? $filters['language'] : language();

    //TODO: fare foreach su options('store_products','listing') dei vari listini per valorizzare contatori e fare pure su tabella per campi
    $query = "
      SELECT
      products.*,
      products_lang.*, 
      manufacturers.name AS manufacturer,
      (SELECT COUNT(id) FROM store_products WHERE id_products = products.id) AS store
    FROM products 
      LEFT JOIN products_lang ON products_lang.id_products = products.id AND products_lang.language = " . encode($language) . "
      LEFT JOIN manufacturers ON manufacturers.id =products.id_manufacturers
     WHERE products.id IS NOT NULL ";

     $query .= $this->applyFilters($filters); 

     return $query;

  }

  /**
   * Inserisce elemento nel database
   *
   * @param array $values
   * @return array|false
   */
  public function create(array $values = [], $reget = true): array|false
  {

    //Inserisco type_data direttamente codificato per non dover fare join su tutte le tabelle
    if(!empty($values['type']) && !empty($values[$values['type']])) {
      $values['type_data'] = json_encode($values[$values['type']]);
    } 

    $this->object = parent::create($values,$reget);

        //Qui è valorizzato l'oggetto, a questo punto inserisco le info sulle tabelle dei tipi prodotto in base al tipo anche sul database
        if(!empty($values['type']) && !empty($values[$values['type']])) {
      Type::insert($this->object['id'],$values['type'],$values[$values['type']]);
    }

    return $this->object;
  }

  /**
   * Modifica oggetto
   *
   * @param integer $id
   * @param array $values
   * @return array
   */
  public function update(int $id, array $values = []): array|false
  {

     //Inserisco type_data direttamente codificato per non dover fare join su tutte le tabelle
    if(!empty($values['type']) && !empty($values[$values['type']])) {
      $values['type_data'] = json_encode($values[$values['type']]);
    } 
  
    $this->object = parent::update($id,$values);

    //Qui è valorizzato l'oggetto, a questo punto inserisco le info sulle tabelle dei tipi prodotto in base al tipo anche sul database
    if(!empty($values['type']) && !empty($values[$values['type']])) {
      Type::insert($this->object['id'],$values['type'],$values[$values['type']]);
    }

    return array_merge($this->object,$values);
  }




}

?>