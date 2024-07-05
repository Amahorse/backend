<?php

declare(strict_types=1);

namespace Elements\Agents\Models;

use Kodelines\Oauth\Scope;
use Kodelines\Abstract\Model;

class AgentsModel extends Model {

  public $table = 'agents';

  public $documents = 'agreements';

  public $uploads = ['file_agreement'];

  public $childs = ['zones','commissions','bonus','products'];

  public $validator = ['id_users' => ['required','unique']];

  public $defaults = [
    'status' => 0
  ];


  /**
   * Query principale
   *
   * @param array $filters
   * @return string
   */
  public function query($filters = []):string {

    $query = "
      SELECT
        agents.*,
        users.type,
        users.first_name,
        users.last_name,
        users.email,
        users.phone,
        users.business_name
      FROM
        agents 
        LEFT JOIN users ON agents.id_users = users.id AND users.auth >= ".Scope::code('agent') . " 
      WHERE agents.id IS NOT NULL 
    ";

    $query.= $this->applyFilters($filters);

    return $query;
  }






}

?>