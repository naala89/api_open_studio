<?php

namespace Gaterdata\Db;

/**
 * Class ApplicationMapper.
 *
 * @package Gaterdata\Db
 */
class ApplicationMapper extends Mapper
{
    /**
     * Save an Application object.
     *
     * @param \Gaterdata\Db\Application $application
     *   The Applicationm object.
     *
     * @return bool
     *   Success.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function save(Application $application)
    {
        if ($application->getAppid() == null) {
            $sql = 'INSERT INTO application (accid, name) VALUES (?, ?)';
            $bindParams = [
            $application->getAccid(),
            $application->getName(),
            ];
        } else {
            $sql = 'UPDATE application SET accid = ?, name = ? WHERE appid = ?';
            $bindParams = [
            $application->getAccid(),
            $application->getName(),
            $application->getAppid(),
            ];
        }
        return $this->saveDelete($sql, $bindParams);
    }

    /**
     * Delete an application.
     *
     * @param \Gaterdata\Db\Application $application
     *   Application object.
     *
     * @return bool
     *   Success.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function delete(Application $application)
    {
        $sql = 'DELETE FROM application WHERE appid = ?';
        $bindParams = [$application->getAppid()];
        return $this->saveDelete($sql, $bindParams);
    }

    /**
     * Find applications.
     *
     * @return array
     *   Array of Application objects.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findAll()
    {
        $sql = 'SELECT * FROM application';
        $bindParams = [];
        return $this->fetchRows($sql, $bindParams);
    }

    /**
     * Find application by application ID.
     *
     * @param int $appid
     *   Application ID.
     *
     * @return \Gaterdata\Db\Application
     *   Application object.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findByAppid($appid)
    {
        $sql = 'SELECT * FROM application WHERE appid = ?';
        $bindParams = [$appid];
        return $this->fetchRow($sql, $bindParams);
    }

    public function findByUid($uid)
    {
        $userRoleMapper = new UserRoleMapper($this->db);
        if ($userRoleMapper->hasRole($uid, 'Administrator')) {
            return $this->findAll();
        }
        if ($userRoleMapper->hasRole($uid, 'Account manager')) {
            $userRoles = $userRoleMapper->
        }
        $sql = 'SELECT DISTINCT a.*';
        $sql .= 'FROM application AS a';
        $sql .= 'INNER JOIN user_role AS ur';
        $sql .= 'ON ur.appid = a.appid';
        $sql .= 'WHERE ur.uid = ?';
        $bindParams = [$uid];
        $result = $this->fetchRows($sql, $bindParams);
    }

    /**
     * Find application by account ID and application name.
     *
     * @param int $accid
     *   Account ID.
     * @param string $name
     *   Application name.
     *
     * @return \Gaterdata\Db\Application
     *   Application object.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findByAccidAppname($accid, $name)
    {
        $sql = 'SELECT * FROM application WHERE accid = ? AND name = ?';
        $bindParams = [
        $accid,
        $name,
        ];
        return $this->fetchRow($sql, $bindParams);
    }

    /**
     * Find applications by account ID.
     *
     * @param int $accid
     *   Account ID.
     *
     * @return array
     *   array of mapped Application objects.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findByAccid($accid)
    {
        $sql = 'SELECT * FROM application WHERE accid = ?';
        $bindParams = [$accid];
        return $this->fetchRows($sql, $bindParams);
    }

    /**
     * Find applications by multiple account IDs and/or application names.
     *
     * @param array $accids
     *   Array of account IDs.
     * @param array $accids
     *   Array of account IDs.
     * @param array $params
     *   parameters (optional)
     *     [
     *       'keyword' => string,
     *       'sort_by' => string,
     *       'direction' => string "ASC"|"DESC",
     *       'start' => int,
     *       'limit' => int,
     *     ]
     *
     * @return array
     *   array of mapped Application objects.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findByAccidsAppnames(array $accids = [], array $appNames = [], array $params = [])
    {
        $byAccid = [];
        $bindParams = [];
        $where = [];
        $orderBy = '';
        $sql = 'SELECT * FROM application';

        foreach ($accids as $accid) {
            $byAccid[] = '?';
            $bindParams[] = $accid;
        }
        if (!empty($byAccid)) {
            $where[] = 'accid IN (' . implode(', ', $byAccid) . ')';
        }

        $byAppname = [];
        foreach ($appNames as $appName) {
            $byAppname[] = '?';
            $bindParams[] = $appName;
        }
        if (!empty($byAppname)) {
            $where[] = 'name IN (' . implode(', ', $byAppname) . ')';
        }

        if (!empty($params['filter']) && !empty($params['filter']['column']) && !empty($params['filter']['keyword'])) {
            $where[] = mysqli_real_escape_string($this->db->_connectionID, $params['filter']['column'])  . ' = ?';
            $bindParams[] = $params['filter']['keyword'];
        }

        if (!empty($params['keyword'])) {
            $where[] = 'name like ?';
            $bindParams[] = $params['keyword'];
        }
        if (!empty($params['order_by'])) {
            $orderBy .= ' ORDER BY ' . mysqli_real_escape_string($this->db->_connectionID, $params['order_by']);
            if (!empty($params['direction'])) {
                $orderBy .= ' ' . strtoupper(mysqli_real_escape_string($this->db->_connectionID, $params['direction']));
            }
        }
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= $orderBy;

        return $this->fetchRows($sql, $bindParams);
    }

    /**
     * Map a DB row into an Application object.
     *
     * @param array $row
     *   DB row object.
     *
     * @return \Gaterdata\Db\Application
     *   Application object
     */
    protected function mapArray(array $row)
    {
        $application = new Application();

        $application->setAppid(!empty($row['appid']) ? $row['appid'] : null);
        $application->setAccid(!empty($row['accid']) ? $row['accid'] : null);
        $application->setName(!empty($row['name']) ? $row['name'] : null);

        return $application;
    }
}
