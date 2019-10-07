<?php

namespace Gaterdata\Db;

use Gaterdata\Core\ApiException;
use Cascade\Cascade;
use ADOConnection;

abstract class Mapper {

  protected $db;

  /**
   * Mapper constructor.
   *
   * @param \ADOConnection $dbLayer
   *   DB connection object.
   */
  public function __construct(ADOConnection $dbLayer) {
    $this->db = $dbLayer;
  }

  /**
   * Map a DB row into an object.
   *
   * @param array $row
   *   DB row.
   *
   * @return mixed
   */
  abstract protected function mapArray(array $row);

  /**
   * Perform a save or delete.
   *
   * @param string $sql
   *   Query string.
   * @param array $bindParams
   *   Array of bind params.
   *
   * @return bool
   *   Success status.
   *
   * @throws ApiException
   */
  protected function saveDelete($sql, array $bindParams) {
    $this->db->Execute($sql, $bindParams);
    if ($this->db->affected_rows() !== 0) {
      return TRUE;
    }
    if (empty($this->db->ErrorMsg())) {
      throw new ApiException('Affected rows: 0, no error message returned. There was possibly nothing to update', 2);
    }
    $message = $this->db->ErrorMsg() . ' (' .  __METHOD__ . ')';
    Cascade::getLogger('gaterdata')->error($message);
    throw new ApiException($message, 2);
  }

  /**
   * Perform an SQL statement that expects a single row.
   *
   * @param string $sql
   *   Query string.
   * @param array $bindParams
   *   Array of bind params.
   *
   * @return mixed
   *   Mapped row.
   *
   * @throws ApiException
   */
  protected function fetchRow($sql, $bindParams) {
    $row = $this->db->GetRow($sql, $bindParams);
    if ($row === FALSE) {
      $message = $this->db->ErrorMsg() . ' (' .  __METHOD__ . ')';
      Cascade::getLogger('gaterdata')->error($message);
      throw new ApiException($message, 2);
    }
    return $this->mapArray($row);
  }

  /**
   * Perform an SQL statement that expects multiple rows.
   *
   * @param string $sql
   *   Query string.
   * @param array $bindParams
   *   Array of bind params.
   * @param array $params
   *   parameters (optional)
   *     [
   *       'filter' => [
   *         'keyword' => string,
   *         'column' => string,
   *       ]
   *       'order_by' => string,
   *       'direction' => string "ASC"|"DESC",
   *       'offset' => int,
   *       'limit' => int,
   *     ]
   * NOTE:
   *   * ['filter']['keyword'] '%' characters in keyword not added to keyword automatically.
   *
   * @return array
   *   Array of mapped rows.
   *
   * @throws ApiException
   */
  protected function fetchRows($sql, $bindParams, array $params = []) {
    // Add filter by keyword.
    if (!empty($params['filter'])) {
      $arr = [];
      foreach ($params['filter'] as $filter) {
        if (isset($filter['column']) && isset($filter['keyword'])) {
          $arr[] = mysqli_real_escape_string($this->db->_connectionID, $filter['column']) . ' LIKE ?';
          $bindParams[] = $filter['keyword'];
        }
      }
      if (!empty($arr)) {
        $sql .= ' WHERE ' . implode(' AND ', $arr);
      }
    }

    // Add order by.
    if (!empty($params['order_by'])) {
      $orderBy = mysqli_real_escape_string($this->db->_connectionID, $params['order_by']);
      $direction = strtoupper(mysqli_real_escape_string($this->db->_connectionID, $params['direction']));
      $sql .= " ORDER BY $orderBy $direction";
    }

    // Add limit.
    if (!empty($params['offset']) || !empty($params['limit'])) {
      $recordSet = $this->db->selectLimit($sql, (integer) $params['limit'], (integer) $params['offset'], $bindParams);
    }

    $recordSet = $this->db->Execute($sql, $bindParams);
    if (!$recordSet) {
      $message = $this->db->ErrorMsg() . ' (' .  __METHOD__ . ')';
      Cascade::getLogger('gaterdata')->error($message);
      throw new ApiException($message, 2);
    }

    $entries = [];
    while (!$recordSet->EOF) {
      $entries[] = $this->mapArray($recordSet->fields);
      $recordSet->moveNext();
    }

    return $entries;
  }

}
