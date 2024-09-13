<?php

declare(strict_types=1);

namespace Kodelines\Abstract;

use Kodelines\Db;
use Kodelines\Tools\Str;
use Kodelines\Tools\Validate;
use Kodelines\Tools\File;
use Kodelines\Helpers\Upload;
use Kodelines\Helpers\Thumbnails;
use Kodelines\Interfaces\ModelInterface;
use Kodelines\Exception\RuntimeException;
use Kodelines\Exception\ValidatorException;

abstract class Model implements ModelInterface
{

  /**
   * Questo array contiene nomi tabelle riservate per crud
   */
  protected const RESERVED = ['tokens', 'users_keys', 'users_security'];

  /**
   * Variabile per tabella principale del model, da settare obbligatoriamente in classe che estende questa
   * 
   * @string
   */
  public $table;


  /**
   * Contiene path dentro cartella uploads per caricare i files
   * 
   * @string
   */
  public $documents;

  /**
   * Campi default
   *
   * @return void
   */
  public $defaults = [];



  /**
   * Campi nascosti
   *
   * @return void
   */
  public $uploads = [];

  /**
   * Contiene istanza uploader
   *
   */
  public $upload;

  /**
   * Array per regole validatore
   *
   * @var array
   */
  public $validator = [];

  /**
   * Contiene nomi campi con tipo presi dal db
   *
   * @var array
   */
  public $fields = [];


  /**
   * Contiene tabelle figlie, devono chiamrsi tabellamadre_tabella e contenere un campo id_tabellamadre
   *
   * @var array
   */
  public $childs = [];

  /**
   * Se multilingua inserisce valori e considera in join query anche tabella multilingua
   *
   * @var boolean
   */
  public $multilanguage = false;


  /**
   * Contiene oggetto per modifica o elimina, può essere settato nel controller per non essere preso dal db due volte
   *
   * @var array
   */
  public $object = [];

  

  /**
   * Il costruttore setta i campi con tipo
   */
  public function __construct()
  {
 
    if (empty($this->table)) {
      throw new RuntimeException('Empty table in model for class '. get_called_class());
    }

    if (in_array($this->table, self::RESERVED)) {
      throw new RuntimeException($this->table . 'Not allowed for being a Model');
    }

    if(Db::tableExists($this->table . '_lang')) {
      $this->multilanguage = true;
    }

    //Merge dei dati se già presenti in model altri campi presi da join altre tabelle
    $this->fields = array_merge($this->fields,Db::getFieldsTypes($this->table));

    $this->upload = new Upload; 
  
  }


  /**
   * Questo serve ad avere shortcuts di metodi 
   *
   * @param [type] $method
   * @param [type] $args
   * @return void
   */
  public function __call($method, $args)
  {
   
    //Controllo metodo se è qualche getChild, deve avere il formato classe::getNometabellachild(id);
    if(Str::startsWith($method,'get') && !empty($args[0]) && is_numeric($args[0])) {

      $child = Db::camelCaseToTable(str_replace($method,'get',''));

      if(in_array($child,$this->childs)) {
        return $this->getChild($child,$args[0]);
      }

    }
  
  } 



  /**
   * Ritorna una stringa con query base per la tabella facendo join a tabella lingua se presente e applicando filtri
   *
   * @param  array $filters
   * @return string
   */
  public function query(array $filters = []): string
  {

    $query = "SELECT m.* FROM " . $this->table;

    //Se una tabella ha multilingua applico join e filtri
    if ($this->multilanguage) {

      if (!empty($filters['lang'])) {
        $language = $filters['lang'];
      } else {
        $language = language();
      }

      $query = "SELECT " . $this->table . ".*," . $this->table . "_lang.* FROM " . $this->table . " LEFT JOIN " . $this->table . "_lang ON " . $this->table . ".id = " . $this->table . "_lang.id_" . $this->table . " AND " . $this->table . "_lang.language = " . encode($language);
    
    } else {

      $query = "SELECT " . $this->table . ".* FROM " . $this->table;
    }

    //TODO: questo è bruttissimo ma è per evitare di fare controllo se settato WHERE prima per ogni filtro
    if (!empty($filters)) {

      $query .= " WHERE " . $this->table . ".id IS NOT NULL ";

      $query .= $this->applyFilters($filters);
    }


    return $query;
  }

  /**
   * Applica filtri su tabella standard
   *
   * @param array $filters
   * @param bool $onlyWhere se true ritorna solo where senza orderby o limit anche se settati
   * @return string
   */
  public function applyFilters(array $filters, $onlyWhere = false):string {

    $query = "";

    foreach($filters as $field => $value) {

      if(!is_string($field)) {
        continue;
      }

      //Prima controllo multilingua perchè sennò muore su array_key_exists sotto
      if($this->multilanguage) {

        $language = !empty($filters['language']) ? $filters['language'] : language();

        if($field == 'slug' && $value === true) {
          $query .= " AND ".$this->table."_lang.slug IS NOT NULL AND  ".$this->table."_lang.language = " . encode($language);
        }

        if($field == 'slug' && is_string($value)) {
          $query .= " AND ".$this->table."_lang.slug = ". encode($value) . " AND ".$this->table."_lang.language = " . encode($language);;
        }

        if($field == 'indexable' && !empty($value)) {
          $query .= " AND ".$this->table."_lang.indexable = 1";
        }

        if($field == 'merchant' && !empty($value)) {
          $query .= " AND ".$this->table."_lang.merchant = 1";
        }

        //Tag filters on language table
        if(!$field == 'tags' && !empty($filters['tags'])) {

          if(is_array($filters['tags'])) {

            $query .= " AND ( ";

            foreach($filters['tags'] as $flag) {

            if(empty($flag)) {
              continue;
            }

            $query .= " LOWER(".$this->table."_lang.tags) LIKE '%". trim(mb_strtolower($flag)) ."%' OR ";
            }

            $query .= " lang.tags = 0 )";

          } else {

            $query .= " AND LOWER(".$this->table."_lang.tags) LIKE '%".trim(mb_strtolower($filters['tags']))."%' ";

          }

        }

      }


      //Orderby e limit si comportano diversamente
      if($field == 'orderby' || $field == 'limit' || $field == 'groupby') {
        continue;
      }


      //Il filtro su query è settato come da... diverso quindi cambio operatore delle operazioni successive
      if(Str::endsWith($field,':not')) {  

        $operator = '<>';

        //Rimuovo il :not dal nome campo
        $field = str_replace(':not','',$field);

      } else {
        $operator = '=';
      }


      //Se non esiste campo continuo
      if(!array_key_exists($field,$this->fields)) { 
        continue;
      }

 
      //Filtro per campo stringa vuota non viene considerato
      if(is_string($value) && $value == '') {  
        continue;
      }


      //Controllo se valore è nullo
      if($value === NULL) { 
 
        if($operator == '=') {
          $query .= " AND ".$this->table.".".$field." IS NULL ";
        } else {
          $query .= " AND ".$this->table.".".$field." IS NOT NULL ";
        }

        continue;
      }

      

      //Applico filtro campo in base ai tipi database
      if($this->fields[$field] == 'integer' || $this->fields[$field] == 'tinyint') {

        //Filtri per id vuoti o uguali a 0 non vengolo applicati
        if(Str::startsWith($field,'id') && empty($value)) {
          continue;
        }


        $query .= " AND ".$this->table.".".$field." ".$operator." " . (int)$value;

      } elseif($this->fields[$field] == 'float') {

        $query .= " AND ".$this->table.".".$field." ".$operator." " . (float)$value;

      } elseif($this->fields[$field] == 'set') {

        if($operator == '=') {
          $query .= " AND FIND_IN_SET(".encode($value).",".$this->table.".".$field.") ";
        } else {
          $query .= " AND NOT FIND_IN_SET(".encode($value).",".$this->table.".".$field.") ";
        }

      } else {
          
        $query .= " AND ".$this->table.".".$field." ".$operator." " . encode($value);
      }

    }



    //Per alcune query si applicano solo filtri standard
    if($onlyWhere == false) {

      if(!empty($filters['groupby'])) {
        $query .= " GROUP BY " . $filters['groupby'];
      }

      if(!empty($filters['orderby'])) {
        $query .= " ORDER BY " . $filters['orderby'];
      } else {
        $query .= " ORDER BY ".$this->table.".id DESC ";
      }
  
      if(!empty($filters['limit'])) {
        $query .= " LIMIT " . (int)$filters['limit'];
      }

      if(!empty($filters['offset'])) {
        $query .= " OFFSET " . (int)$filters['offset'];
      }

    }

    return $query;
  }

  /**
   * Get a Single row by id 
   *
   * @method get
   * @param  $id    id elemento
   * @return array|false
   */
  public function get(int $id, $filters = []): array|false
  {

    //Controlla se l'oggetto richiesto è già quello istanziato
    if(!empty($this->object) && $this->object['id'] == $id) {
      return $this->object;
    }

    if(!$this->object = Db::getRow($this->query(array_merge($filters,['id' => $id, 'groupby' => $this->table . '.' . 'id'])))) {
      return false;
    }

    return $this->object;
  }



  /**
   * Get a Single row by slug (only for tables with _lang)
   *
   * @method get
   * @param  $slug   slug elemento
   * @return array|false
   */
  public function slug(string $slug = '', $filters = []): array|false
  {

    if(!$data = Db::getRow($this->query(array_merge($filters,['slug' => $slug])))) {
      return false;
    }

    return $data;
  }


  /**
   * Ritorna lista di occorrenze con filtri applicabili a query principale
   *
   * @param  array $filters
   * @return array
   */
  public function list($filters = []):array
  {

  
    if(!$data = Db::getArray($this->query($filters))) {
      return [];
    }


    return $data;

  }

  /**
   * Torna lista, su qualche modell serve a fare sotto query
   *
   * @param  array $filters
   * @return array
   */
  public function fullList($filters = []):array {

    $data = [];

    foreach($this->list($filters) as $key => $value) {
      $data[] = $this->fullGet($value['id']);
    }

    return $data;
  }


  /**
   * Shortcut che ritorna unica riga tramite parametro univoco settato o lista
   *
   * @param string $param
   * @param mixed $value
   * @return array
   */
  public function where(string $param, mixed $value): mixed
  {

    //Su campi unici ritorna get row, è una scorciatoia
    if($param == 'id' || (isset($this->validator[$param]) && in_array('unique', $this->validator[$param]))) {
      
      if(!$data = Db::getRow($this->query([$param => $value]))) {
        return false;
      }

      return $data;
    }

    if(!$data = Db::getArray($this->query([$param => $value]))) {
      return false;
    }

    return $data;

  }


  /**
   * Inserisce elemento nel database
   *
   * @param array $values
   * @param bool  $reget Se true rifa il get del valore dopo l'insert per riprendere l'oggetto completo di valori default
   * @return array|false
   */
  public function create(array $values = [], $reget = true): array|false
  {

    //Id in array non può essere inserito o modificato
    if(isset($values['id'])) {
      unset($values['id']);
    }

    $values = $this->setDefaults($values);

    $this->validate($values);

    //Carico uploads se trovati, NB: funziona solo caricando file su campi in base64 da front end, 
    //per caricare file normali  su $_FILES fare direttamente da controller con nuova istanza uploads
    foreach($this->uploads as $field) {

      //Se il valore ha estensione vuol dire che non è in base64 quindi è già stato caricato in precedenza o si sta tentando di sovrasrivere il valore
      if(!empty($values[$field]) && (!empty(File::extension($values[$field])))) {
        continue;
      }

      if(empty($values[$field]) || !$values[$field] = $this->upload->start($values[$field],$this->documents)) {
        $values[$field] = NULL;
      }

    }

    if(!$values['id'] = Db::insert($this->table,$values)) { 
      return false;
    }

    if($this->multilanguage) {
      $values = array_merge($values,Db::insertLanguages($values,$this->table,$values['id']));
    }

    //Insert images if set
    if(isset($values['images']) && is_array($values['images']) && Db::tableExists($this->table . '_images')) {
        $this->setImages($values['images'], $values['id']);
    }
      

    foreach ($this->childs as $child) {

      //Tabelle
      $idc = 'id_' . $this->table;

      $table = $this->table .'_'. $child;

      //Insert contacts
      if(isset($values[$child]) && is_array($values[$child]) && Db::tableExists($table)) {
        Db::insertMultiple($values[$child],$table, $idc, $values['id']);
      }
    }

    //Forzo l'oggetto corrente a resettarsi con quello nuovo
    $this->object = [];
   

    //Qua rifaccio get a differenza di update perchè non ho i valori auto creati dal database o default e li riprendo rifacendo query
    if($reget) { 
      
      if(!$this->object = $this->get(id($values['id']))) { 
        return false;
      }

    } else {
      $this->object = $values;
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

    //Id in array non può essere inserito o modificato
    if(isset($values['id'])) {
      unset($values['id']);
    }

    if(!$this->object = $this->get($id)) { 
      return false;
    }

    if(empty($values)) {
      return $this->object;
    }

    $this->validate($values);


    //foreach per caricamento files ed eliminazione vecchie thumbnails
    foreach($this->uploads as $field) {

      if(!isset($values[$field])) {
        continue;
      }

      if($values[$field] == $this->object[$field]) {
        continue;
      }

      if(empty($values[$field]) || !$values[$field] = $this->upload->replace($this->object[$field])->start($values[$field],$this->documents)) {
        $values[$field] = $this->object[$field];
      }

    }

    if(!Db::updateArray($this->table,$values,'id',$id)) {    
      return false;
    }

    if($this->multilanguage) {
      $values = array_merge($values,Db::insertLanguages($values,$this->table,$id ));
    }

    //Insert images if set
    if(isset($values['images']) && is_array($values['images']) && Db::tableExists($this->table . '_images')) {
      $this->setImages($values['images'], $id);
    }

 
    foreach ($this->childs as $child) {

      //Tabelle
      $idc = 'id_' . $this->table;

      $table = $this->table .'_'. $child;

      //Questo è per fare check se è stato creato il clonabile da backoffice, su cloneableStart crea un input hidden che serve a capire se è stato inviato con tutto cancellato, 
      //questo perchè il clonabile js non manda array vuoto ma cancella il campo se si elimina l'ultimo clonabile
      $check = 'check_' . $child ;

      if(isset($check) && !isset($values[$child])) {
        $values[$child] = [];
      }

      //Inserisco roba in tabelle child
      if(isset($values[$child]) && is_array($values[$child]) && Db::tableExists($table)) {
        Db::insertMultiple($values[$child],$table, $idc, $id,Db::getArray("SELECT * FROM ".$table." WHERE ".$idc." = " .$id));
      }



    }

    $this->object = array_merge($this->object,$values);

    return $this->object;
  }

  /**
   * Cancella un record
   *
   * @param integer $id
   * @return boolean
   */
  public function delete(int $id): bool
  {
    
    if(!$this->object = $this->get($id)) {
      return false;
    }

    $thumbnails = new Thumbnails;

    if(!empty($this->uploads) && !empty($this->documents)) {

      foreach($this->uploads as $field) {

        if(!empty($this->object[$field])) {

          if(file_exists($this->documents . '/' . $this->object[$field])) {
            unlink($this->documents . '/' . $this->object[$field]);
          } 

          $thumbnails->delete($this->object[$field],$this->documents);

        }

  
      }

    }

    if(!Db::tableExists($this->table . '_images')) {

      //Delete images
      if($images = $this->getImages($id)) {
  
        foreach($images as $image) {
  
          if(!empty($image['image'])) {
            $thumbnails->delete($image['image'],$this->documents);
          }
    
        }
        
      }
      

    }

    //Resetto l'oggetto perchè non esiste più
    $this->object = [];

    return Db::delete($this->table,'id',$id);

  }


  /**
   * Validatore campi
   *
   * @param array $fields
   * @param bool $dryrun se a true fa un dry run del validatore, non torna eccezzioni ma false se c'è un campo non valido
   * @return array
   */
  public function validate(array $fields, bool $dryrun = false):bool {

    foreach($this->validator as $field => $params) {
      
      if(in_array('required',$params)) {

        if((!isset($this->object[$field]) || $this->object[$field] === '' || $this->object[$field] === null) && (!isset($fields[$field]) || $fields[$field] === '' || $fields[$field] === null)) {
   
          if($dryrun) {
            return false;
          }

          throw new ValidatorException('missing_required_fields',$field);
        }
      }

      if(in_array('unique',$params)) {

  

        if(!empty($fields[$field]) && empty($this->object[$field]) && !Validate::uniqField($field,$fields[$field],$this->table, isset($this->object['id']) ? $this->object['id'] : false)) {

          if($dryrun) {
            return false;
          }

          throw new ValidatorException('field_already_exists',$field);
        }
      }

      if(in_array('email',$params)) {

        if(!empty($fields[$field]) && !Validate::isEmail($fields[$field])) {

          if($dryrun) {
            return false;
          }

          throw new ValidatorException('email_not_valid',$field);
        }
      }

      if(in_array('username',$params)) {

        if(!empty($fields[$field]) && !Validate::isUsername($fields[$field])) {

          if($dryrun) {
            return false;
          }

          throw new ValidatorException('username_not_valid',$field);
        }
      }

      if(in_array('phone',$params)) {

        if(!empty($fields[$field]) && !Validate::isPhoneNumber($fields[$field])) {

          if($dryrun) {
            return false;
          }

          throw new ValidatorException('phone_number_not_valid',$field);
        }
      }
     
      //Il cap viene validato solo se tra i campi c'è id nazione per prendere regexp dal database
      //TODO: testare meglio e riabilitare anche lato front end
      if(in_array('zip_code',$params)) {
        /*
        if(!empty($fields[$field]) && !empty($fields['id_countries']) && !Validate::isZipCode($fields[$field],Countries::getZipRegexp(id($fields['id_countries'])))) {

          if($dryrun) {
            return false;
          }
          
          throw new ValidatorException('zip_code_not_valid',$field);
        }
        */
      }
    }

    return true;

  }


  /**
   * Applica valori di default
   *
   * @param array $values
   * @return array $values
   * array
   */
  public function setDefaults(array $values): array {

    foreach($this->defaults as $field => $value) {
      if(empty($values[$field])) {
        $values[$field] = $value;
      }
    }
    
    return $values;
  }

  /**
   * Ritorna valori di default
   *
   * @return array
   */
  public function getDefaults(): array {

    return $this->defaults;
  }


  /**
   * Ritorna cartella documents
   *
   * @return string
   */
  public function getDocuments(): string {

    if(empty($this->documents)) {
      throw new RuntimeException('Empty documents called in model '. get_called_class());
    }

    return $this->documents;
  }


  /**
   * Ritorna oggetto con subarray di tutte le lingue e di tutte le tabelle child
   *
   * @param integer $id
   * @return boolean|array
   */
  public function fullGet(int $id, $filters = []): false|array {

    if(!$object = $this->get($id,$filters)) {
      return false;
    }

    if($this->multilanguage) {

      if(!$languages = Db::getLanguages($id,$this->table)) {
        return $object;
      }

      $object = array_replace($languages,$object);
    }

    foreach($this->childs as $child) {

      $object[$child] = $this->getChild($child,$id);

    }

    //Prendo immagini se esiste
    if(Db::tableExists($this->table . '_images')) {
      $object['images'] = self::getImages($id);
    }

    return $object;

  }

  /**
   * Ritorna i dati di una tabella child con nome formato tabellamadre_tabellafiglia in base a id_tabella
   *
   * @param string $table
   * @param integer $id
   * @return array
   */
  public function getChild(string $table, int $id):array {

    //Insert images if set
    if(!Db::tableExists($this->table."_".$table)) {
      return [];
    }


    return Db::getArray("SELECT * FROM ".$this->table."_".$table." WHERE id_".$this->table." = " . $id);
  }


  /**
   * Ritorna contenuto tabella immagini 
   *
   * @param integer $id
   * @return array
   */
  public function getImages(int $id):array {

   //Insert images if set
   if(!Db::tableExists($this->table . '_images')) {
      return [];
   }

    return Db::getArray("SELECT * FROM ".$this->table."_images WHERE id_".$this->table." = " . $id);
  }


  /**
   * Inserisce immagini multiple per un elemento in una tabella che si chiama nometabellaprincipale_images
   *
   * @param array $images
   * @param integer $id
   * @return void
   */
  public function setImages(array $images, int $id) {


    $new_ids = array();

    $old_ids = array();

    //fetch array for old colors
    foreach($this->getImages($id) as $old) {
      $old_ids[$old['id']] = $old;
    }

    //fetch array for old colors
    foreach($images as $new) {

      if(isset($new['id']) && !empty($new['id'])) {

        $new_ids[$new['id']] = $new;
        
        if(isset($old_ids[$new['id']])) {

          $old = $old_ids[$new['id']];
      
          //Check for different image
          if($old['image'] <> $new['image']) {

            //Upload the new image and insert data to db
            if(empty($new['image']) || !$new['image'] = Upload::create()->replace($old['image'])->imageOnly()->start($new['image'],$this->documents)) {

              $new['image'] = NULL;
            }

          }
 
          Db::updateArray($this->table . '_images',$new,'id',$new['id']);
          
        }


      } else {

        $new['id_' . $this->table] = $id;

        //Upload the new image and insert data to db
        if(empty($new['image']) || !$new['image'] = Upload::create()->imageOnly()->start($new['image'],$this->documents)) {

         continue;

        } else {

          if(Db::insert($this->table . '_images',$new) && $newid = Db::lastInsertId()) {
            $new_ids[$newid] = $new;
          }

        }

      }

    }

    $thumbnails = new Thumbnails;

    //Compare arrays for delete
    foreach($old_ids as $key => $value) {

      if(!isset($new_ids[$key])) {

        if($value['image'] <> '') {
          $thumbnails->delete($value['image'],$this->documents);
        }

        Db::query("DELETE FROM " . $this->table . "_images WHERE id = " . id($key));

      }

    }

  }



  /**
   * Importa campi per validazione e traduzione da un'altro modello e li aggiunge a quelli del modello corrente, che hanno comunque la priorità se duplicati
   *
   * @param object $model
   * @return void
   */
  public function importFields(object $model):void {

    if(!empty($model->fields)) {
      $this->fields = array_merge($model->fields,$this->fields);
    }

  }






}
