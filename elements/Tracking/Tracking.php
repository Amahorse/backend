<?php

declare(strict_types=1);

namespace Elements\Tracking;

use Kodelines\Db;
use Kodelines\Tools\Client;
use Kodelines\Abstract\Decorator;
use Kodelines\Tools\Domain;


class Tracking extends Decorator
{
  /**
   * This class represents the Tracking element.
   * It contains a static property $current that holds the current tracking data.
   */
  public static $current = [];

  /**
   * Builds an array of tracking values.
   *
   * @return array The array of tracking values.
   */
  public static function buildValues(): bool|array
  {

    if (!defined('_OAUTH_TOKEN_JTI_')) {
      return false;
    }

    //TODO: passati a modalità solo api, il dominio è preso da header CMS
    $tracking = [
      'oauth_tokens_jti' => _OAUTH_TOKEN_JTI_,
      'language' => _APP_LANGUAGE_,
      'domain' => Domain::current() 
    ];


    if ($ip = Client::IP()) {
      $tracking['ip'] = $ip;
    }

    if ($os = Client::os()) {
      $tracking['os'] = $os;
    }

    if ($browser = Client::browser()) {
      $tracking['browser'] = $browser;
    }

    if(defined('_ID_AGENTS_')) {
      $tracking['id_agents'] = _ID_AGENTS_;
    }

    if(defined('_ID_RESELLERS_')) {
      $tracking['id_resellers'] = _ID_RESELLERS_;
    }

    if(user('id')) {
      $tracking['id_users'] = user('id');
    }

    if(isset($_GET['cpg'])) {
      $tracking['cpg'] = $_GET['cpg'];
    }

    if(isset($_GET['ref'])) {
      $tracking['ref'] = $_GET['ref'];
    }

    return $tracking;
  }

  /**
   * Retrieves the current tracking information.
   *
   * @return array The current tracking information.
   */
  public static function getCurrent(): array
  {
    if (!config('tracking', 'internal') || defined('_BOT_DETECTED_') || PHP_SAPI == 'cli' || !defined('_OAUTH_TOKEN_JTI_')) {
      return self::$current;
    }

    if (!self::$current = Db::getRow(Tracking::query(["oauth_tokens_jti" => _OAUTH_TOKEN_JTI_, 'limit' => 1]))) {

      if (!$values = self::buildValues()) {
        return [];
      }
      
      if (!self::$current = self::create($values, false)) {
        return [];
      }


    } else {

      $tracking = $_GET ?? [];

      if(isset($tracking['cpg']) || isset($tracking['ref'])) {
        Db::updateArray('tracking', $tracking,'id', self::$current['id']);
      }

    }

    return self::$current;
  }

  /**
   * Retrieves the current ID from the tracking data.
   *
   * @return int|null The current ID if available, otherwise null.
   */
  public static function getCurrentId(): int|null
  {
    self::getCurrent();

    if (empty(self::$current['id'])) {
      return null;
    }

    return id(self::$current['id']);
  }

  /**
   * Retrieve an options list for a given column from the tracking table.
   *
   * @param string $col The column name.
   * @return array The options list.
   */
  public static function optionsList(string $col)
  {
    $list = [];
    $options = Db::getArray("SELECT t." . $col . ", c.name AS cpg_name FROM tracking t LEFT JOIN tracking_cpg c ON t.cpg = c.cpg GROUP BY " . $col);

    foreach ($options as $op) {
      $colValue = empty($op[$col]) ? 'unknown' : $op[$col];
      $list[$colValue] = ($col == 'cpg' && !empty($op['cpg_name'])) ? $op['cpg_name'] : tr($colValue);
    }

    return $list;
  }

  /**
   * Retrieves the filters for tracking.
   *
   * This method retrieves the filters for tracking from the $_GET superglobal array.
   * If the $_GET array is empty, an empty array is returned.
   * If the 'date_to' filter is not set, the current date is used.
   * If the 'date_from' filter is not set, the date 30 days ago is used.
   *
   * @return array The filters for tracking.
   */
  public static function filters()
  {
    $filters = $_GET ?? [];

    if (!isset($filters['date_to'])) {
      $filters['date_to'] = date("Y-m-d");
    }

    if (!isset($filters['date_from'])) {
      $filters['date_from'] = date("Y-m-d", strtotime(date("Y-m-d") . "- 30 days"));
    }

    return $filters;
  }

  /**
   * Retrieves tracking statistics based on the provided filters.
   *
   * @param array $filters An array of filters to apply to the query.
   *                       Possible filters: 'ref', 'date_from', 'date_to'.
   * @return array An array containing the tracking statistics.
   *               The array structure is as follows:
   *               [
   *                 'contacts' => [], // Array of items with 'id_contacts_requests' field
   *                 'subscriptions' => [], // Array of items with 'id_newsletter_subscriptions' field
   *                 'cpg' => [], // Array of items with 'cpg' field
   *                 'actions' => [ // Array of actions grouped by 'action_type' and 'action'
   *                   'action_type' => [
   *                     'action' => [] // Array of items with 'action_type', 'action' fields
   *                   ]
   *                 ]
   *               ]
   */
  public static function stats($filters = [])
  {
    $query = "
      SELECT
        t.id,
        t.ip,
        CASE WHEN t.id_users IS NULL THEN 'guest' ELSE 'user' END AS user_type,
        COALESCE(t.ref, 'unknown') AS ref,
        COALESCE(t.cpg, 'unknown') AS cpg,
        r.id AS id_contacts_requests,
        n.id AS id_newsletter_subscriptions,
        a.action,
        a.type AS action_type,
        a.date_ins
      FROM tracking t
        LEFT JOIN contacts_requests r ON t.id = r.id_tracking
        LEFT JOIN newsletter_subscriptions n ON t.id = n.id_tracking
        LEFT JOIN tracking_actions a ON t.id = a.id_tracking
      WHERE t.id IS NOT NULL";

    if (isset($filters['ref']) && !empty($filters['ref'])) {
      if ($filters['ref'] == 'unknown') {
        $query .= " AND t.ref IS NULL";
      } else {
        $query .= " AND t.ref = " . encode($filters['ref']);
      }
    }

    if (isset($filters['date_from']) && !empty($filters['date_from'])) {
      $query .= " AND DATE(t.date_ins) >= " . encode($filters['date_from']);
    }

    if (isset($filters['date_to']) && !empty($filters['date_to'])) {
      $query .= " AND DATE(t.date_ins) <= " . encode($filters['date_to']);
    }

    $query .= " ORDER BY a.date_ins ASC";

    $stats = [
      "contacts" => [],
      "subscriptions" => [],
      "cpg" => [],
      "actions" => []
    ];

    $items = Db::getArray($query);

    foreach ($items as $item) {
      if (!isset($stats['ref'][$item['ref']])) {
        $stats['ref'][$item['ref']] = 0;
      }
      $stats['ref'][$item['ref']]++;

      if (!isset($stats['cpg'][$item['cpg']])) {
        $stats['cpg'][$item['cpg']] = 0;
      }
      $stats['cpg'][$item['cpg']]++;

      if (!empty($item['id_contacts_requests'])) {
        $stats['contacts'][] = $item;
      }

      if (!empty($item['id_newsletter_subscriptions'])) {
        $stats['subscriptions'][] = $item;
      }

      if (!empty($item['action_type'])) {
        if (!isset($stats['actions'][$item['action_type']])) {
          $stats['actions'][$item['action_type']] = [];
        }

        if (!isset($stats['actions'][$item['action_type']][$item['action']])) {
          $stats['actions'][$item['action_type']][$item['action']] = [];
        }

        $stats['actions'][$item['action_type']][$item['action']][] = $item;
      }
    }

    return $stats;
  }
}

