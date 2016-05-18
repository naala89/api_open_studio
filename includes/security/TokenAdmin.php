<?php

/**
 * Provide token authentication based on token in DB with role admin
 */

namespace Datagator\Security;
use Datagator\Core;
use Datagator\Processor;

class TokenAdmin extends Token {

  protected $role = 'admin';
  protected $details = array(
    'name' => 'Token (Admin)',
    'description' => 'Validate the request, requiring the consumer to have a valid token and a role of admin for application referenced by the appId in the URI.',
    'menu' => 'Security',
    'client' => 'System',
    'application' => 'All',
    'input' => array(
      'token' => array(
        'description' => 'The consumers token.',
        'cardinality' => array(1, 1),
        'accepts' => array('processor')
      )
    ),
  );

  public function process() {
    Core\Debug::variable($this->meta, 'Security TokenAdmin', 4);

    $roles = parent::process();

    foreach ($roles as $role) {
      if ($role->getRid() == $this->role->getRid()) {
        return true;
      }
    }

    throw new Core\ApiException('permission denied', 4, $this->id, 401);
  }
}
