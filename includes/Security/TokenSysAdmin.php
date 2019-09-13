<?php

/**
 * Provide token authentication based on token in DB
 */

namespace Gaterdata\Security;
use Gaterdata\Core;
use Gaterdata\Db;

class TokenSysAdmin extends Core\ProcessorEntity
{
  protected $role = 'SysAdmin';
  protected $details = array(
    'name' => 'Token (SysAdmin)',
    'machineName' => 'tokenSysAdmin',
    'description' => 'Validate the request, requiring the consumer to have a valid token and a role of sys-admin for application referenced by the appId in the URI.',
    'menu' => 'Security',
    'application' => 'Common',
    'input' => array(
      'token' => array(
        'description' => 'The consumers token.',
        'cardinality' => array(1, 1),
        'literalAllowed' => false,
        'limitFunctions' => array(),
        'limitTypes' => array('string'),
        'limitValues' => array(),
        'default' => ''
      )
    ),
  );

  public function process() {
    Core\Debug::variable($this->meta, 'Security TokenSysAdmin', 4);

    // no token
    $token = $this->val('token');
    if (empty($token)) {
      throw new Core\ApiException('permission denied', 4, -1, 401);
    }

    // invalid token or user not active
    $db = $this->getDb();
    $userMapper = new Db\UserMapper($db);
    $user = $userMapper->findBytoken($token);
    if (empty($user->getUid()) || $user->getActive() == 0) {
      throw new Core\ApiException('permission denied', 4, -1, 401);
    }

    // get role from DB
    $roleMapper = new Db\RoleMapper($db);
    $this->role = $roleMapper->findByName($this->role);

    // return list of roles for user for this request app
    $userRoleMapper = new Db\UserRoleMapper($db);
    $roles = $userRoleMapper->findByMixed($user->getUid(), null, $this->role->getRid());
    if (sizeof($roles) > 0) {
      return true;
    }

    throw new Core\ApiException('permission denied', 4, $this->id, 401);
  }
}
