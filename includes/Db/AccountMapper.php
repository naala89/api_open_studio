<?php

namespace Gaterdata\Db;

/**
 * Class AccountMapper.
 *
 * @package Gaterdata\Db
 */
class AccountMapper extends Mapper
{
    /**
     * Save an Account.
     *
     * @param \Gaterdata\Db\Account $account
     *   Account object.
     *
     * @return bool
     *   Success.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function save(Account $account)
    {
        if ($account->getAccid() == null) {
            $sql = 'INSERT INTO account (name) VALUES (?)';
            $bindParams = [$account->getName()];
        } else {
            $sql = 'UPDATE account SET name = ? WHERE accid = ?';
            $bindParams = [
            $account->getName(),
            $account->getAccid(),
            ];
        }
        return $this->saveDelete($sql, $bindParams);
    }

    /**
     * Delete an account.
     *
     * @param \Gaterdata\Db\Account $account
     *   Account object.
     *
     * @return bool
     *   Success.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function delete(Account $account)
    {
        $sql = 'DELETE FROM account WHERE accid = ?';
        $bindParams = [$account->getAccid()];
        return $this->saveDelete($sql, $bindParams);
    }

    /**
     * Find an accounts.
     *
     * @param array|NULL $params
     *   @see Gaterdata\Db\Mapper.
     *
     * @return array
     *   array Account objects.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findAll($params = [])
    {
        $sql = 'SELECT * FROM account';
        return $this->fetchRows($sql, [], $params);
    }

    /**
     * Find an account by ID.
     *
     * @param int $accid
     *   Account Id.
     *
     * @return \Gaterdata\Db\Account
     *   Account object.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findByAccid($accid)
    {
        $sql = 'SELECT * FROM account WHERE accid = ?';
        $bindParams = [$accid];
        return $this->fetchRow($sql, $bindParams);
    }

    /**
     * Find accounts by IDs.
     *
     * @param Array $accids
     *   Account Ids.
     *
     * @return Array
     *   Array of Account objects.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findByAccids(array $accids)
    {
        $inAccid = [];
        foreach ($accids as $accid) {
            $inAccid[] = '?';
        }
        $sql = 'SELECT * FROM account';
        if (!empty($inAccid)) {
            $sql .= ' WHERE accid IN (' . implode(', ', $inAccid) . ')';
        }
        return $this->fetchRow($sql, $accids);
    }

    /**
     * Find an account by name.
     *
     * @param string $name
     *   Account name.
     *
     * @return \Gaterdata\Db\Account
     *   Account object.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findByName($name)
    {
        $sql = 'SELECT * FROM account WHERE name = ?';
        $bindParams = [$name];
        return $this->fetchRow($sql, $bindParams);
    }

    /**
     * Find an accounts by names.
     *
     * @param array $names
     *   Account names.
     *
     * @return \Gaterdata\Db\Account
     *   Account object.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findByNames(array $names = [])
    {
        $arr = [];
        foreach ($names as $name) {
            $arr[] = '?';
        }
        $sql = 'SELECT * FROM account WHERE name IN (' . implode(', ', $arr) . ')';
        $bindParams = $names;
        return $this->fetchRows($sql, $bindParams);
    }

    /**
     * Find an accounts by a user has roles for.
     *
     * @param integer $uid
     *   User ID.
     *
     * @return Array
     *   Array of Account objects.
     *
     * @throws \Gaterdata\Core\ApiException
     */
    public function findAllForUser($uid, $params = [])
    {
        $sql = 'SELECT *';
        $sql .= ' FROM account';
        $sql .= ' WHERE accid';
        $sql .= ' IN (';
        $sql .= ' SELECT accid';
        $sql .= ' FROM account';
        $sql .= ' WHERE EXISTS';
        $sql .= ' (';
        $sql .= ' SELECT *';
        $sql .= ' FROM user_role AS ur';
        $sql .= ' INNER JOIN role AS r';
        $sql .= ' ON ur.rid = r.rid';
        $sql .= ' WHERE ur.uid = ?';
        $sql .= ' AND r.name = "Administrator"';
        $sql .= ' )';
        $sql .= ' UNION DISTINCT';
        $sql .= ' SELECT a.accid';
        $sql .= ' FROM account AS a';
        $sql .= ' INNER JOIN user_role AS ur';
        $sql .= ' ON a.accid = ur.accid';
        $sql .= ' WHERE ur.uid = ?';
        $sql .= ' )';
        $bindParams = [$uid, $uid];
        return $this->fetchRows($sql, $bindParams, $params);
    }

    /**
     * Map a DB row into an Account object.
     *
     * @param array $row
     *   DB row object.
     *
     * @return \Gaterdata\Db\Account
     *   Account object.
     */
    protected function mapArray(array $row)
    {
        $account = new Account();

        $account->setAccid(!empty($row['accid']) ? $row['accid'] : null);
        $account->setName(!empty($row['name']) ? $row['name'] : null);

        return $account;
    }
}
