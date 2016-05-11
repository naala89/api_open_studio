<?php

/**
 * User table CRUD.
 */

namespace Datagator\Processor;
use Datagator\Core;
use Datagator\Db;

class DatagatorUser extends ProcessorBase
{
  protected $details = array(
    'name' => 'Datagator User',
    'description' => 'CRUD operations for Datagator users.',
    'menu' => 'Admin',
    'application' => 'Admin',
    'input' => array(
      'username' => array(
        'description' => 'The username of the user.',
        'cardinality' => array(1, 1),
        'accepts' => array('processor', 'literal'),
      ),
      'active' => array(
        'description' => 'The status of the user account. Only used for creating user',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', '"0"', '"1"'),
      ),
      'email' => array(
        'description' => 'The email of the user. Only used for creating user',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', 'string'),
      ),
      'honorific' => array(
        'description' => 'The honorific of the user. Only used for creating user',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', 'string'),
      ),
      'nameFirst' => array(
        'description' => 'The first name of the user. Only used for creating user',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', 'string'),
      ),
      'nameLast' => array(
        'description' => 'The last name of the user. Only used for creating user',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', 'string'),
      ),
      'company' => array(
        'description' => 'The company of the user Only used for creating user',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', 'string'),
      ),
    ),
  );

  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor DatagatorUser', 4);

    $username = $this->val($this->meta->username);
    $method = $this->request->method;
    $db = $this->getDb();

    $userMapper = new Db\UserMapper($db);
    $user = $userMapper->findByUsername($username);

    switch ($method) {
      case 'post':
        $user->setUsername($username);
        $active = $this->val($this->meta->active);
        $active = !empty($active) ? $active : 1;
        $user->setActive($active);
        $user->setEmail($this->val($this->meta->email));
        $user->setHonorific($this->val($this->meta->honorific));
        $user->setNameFirst($this->val($this->meta->nameFirst));
        $user->setNameLast($this->val($this->meta->nameLast));
        $user->setCompany($this->val($this->meta->company));
        return $userMapper->save($user);
        break;
      case 'get':
        return $user->debug();
        break;
      case 'delete':
        return $userMapper->delete($user);
        break;
      default:
        throw new Core\ApiException('Invalid action', 1, $this->id);
        break;
    }
  }
}
