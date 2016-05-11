<?php

/**
 * Account table CRUD.
 */

namespace Datagator\Processor;
use Datagator\Core;
use Datagator\Db;

class DatagatorAccount extends ProcessorBase
{
  protected $details = array(
    'name' => 'Datagator Account',
    'description' => 'CRUD operations for Datagator accounts.',
    'menu' => 'Admin',
    'application' => 'Admin',
    'input' => array(
      'accountName' => array(
        'description' => 'The name of the account.',
        'cardinality' => array(1, 1),
        'accepts' => array('processor', 'literal'),
      ),
      'username' => array(
        'description' => "The username to associate with the account.",
        'cardinality' => array(0, 1),
        'accepts' => array('processor', 'literal'),
      ),
    ),
  );

  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor DatagatorAccount', 4);

    $accountName = $this->val($this->meta->accountName);
    $method = $this->request->method;
    $db = $this->getDb();

    $uid = '';
    if (!empty($this->meta->username)) {
      $username = $this->val($this->meta->username);
      $userMapper = new Db\UserMapper($db);
      $user = $userMapper->findByUsername($username);
      $uid = $user->getUid();
      if (empty($uid)) {
        throw new Core\ApiException("No such user: $username", 1, $this->id);
      }
    }

    $accountMapper = new Db\AccountMapper($db);

    switch ($method) {

      case 'post':
        $account = $accountMapper->findByUidName($uid, $accountName);
        $account->setUid($uid);
        $account->setName($accountName);
        return $accountMapper->save($account);
        break;

      case 'get':
        if (!isset($uid)) {
          if (!isset($accountName)) {
            $account = new Db\Account();
          } else {
            $account = $accountMapper->findByName($accountName);
          }
        } else {
          if (!isset($accountName)) {
            $account = $accountMapper->findByUid($uid);
          } else {
            $account = $accountMapper->findByUidName($uid, $accountName);
          }
        }
        return $account->debug();
        break;

      case 'delete':
        $account = $accountMapper->findByName($accountName);
        return $accountMapper->delete($account);
        break;

      default:
        throw new Core\ApiException('Invalid action', 1, $this->id);
        break;
    }
  }
}
