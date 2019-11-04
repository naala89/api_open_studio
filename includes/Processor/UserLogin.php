<?php

/**
 * Provide token authentication based on token in DB
 */

namespace Gaterdata\Processor;
use Gaterdata\Core\Config;
use Gaterdata\Core;
use Gaterdata\Db;

class UserLogin extends Core\ProcessorEntity
{
  protected $details = [
    'name' => 'User Login',
    'machineName' => 'user_login',
    'description' => 'Login a user. Token and uid returned.',
    'menu' => 'Validator',
    'application' => 'Common',
    'input' => [
      'username' => [
        'description' => 'Users username.',
        'cardinality' => [1, 1],
        'literalAllowed' => false,
        'limitFunctions' => [],
        'limitTypes' => ['string'],
        'limitValues' => [],
        'default' => ''
      ],
      'password' => [
        'description' => 'Users password.',
        'cardinality' => [1, 1],
        'literalAllowed' => false,
        'limitFunctions' => [],
        'limitTypes' => ['string'],
        'limitValues' => [],
        'default' => ''
      ],
    ],
  ];

  /**
   * @return mixed|string
   * @throws \Gaterdata\Core\ApiException
   * @throws \Gaterdata\Processor\ApiException
   */
  public function process() {
    Core\Debug::variable($this->meta, 'Processor ' . $this->details()['machineName'], 2);

    $username = $this->val('username', TRUE);
    $password = $this->val('password', TRUE);
    $userMapper = new Db\UserMapper($this->db);

    // Validate username and active status.
    $user = $userMapper->findByUsername($username);
    Core\Debug::variable($user, 'user');
    if (empty($user->getUid()) || $user->getActive() == 0) {
      throw new Core\ApiException('invalid username or password', 4, $this->id, 401);
    }

    // No password hash stored yet.
    if (empty($storedHash = $user->getHash())) {
      throw new Core\ApiException('invalid username or password', 4, $this->id, 401);
    }
    $hash = Core\Hash::generateHash($password);
    if (!Core\Hash::verifPassword($password, $storedHash)) {
      throw new Core\ApiException('invalid username or password', 4, $this->id, 401);
    }

    // if token exists and is active, return it
    $config = new Config();
    $tokenLife = $config->__get(['api', 'token_life']);
    if (!empty($user->getToken())
      && !empty($user->getTokenTtl())
      && Core\Utilities::date_mysql2php($user->getTokenTtl()) > time()) {
      $user->setTokenTtl(Core\Utilities::date_php2mysql(strtotime($tokenLife)));
      $userMapper->save($user);
      return new Core\DataContainer(
        ['token' => $user->getToken(), 'uid' => $user->getUid()],
        'array'
      );
    }

    //perform login
    $user->setHash($hash);
    $token = Core\Hash::generateToken($username);
    $user->setToken($token);
    $user->setTokenTtl(Core\Utilities::date_php2mysql(strtotime($tokenLife)));
    $userMapper->save($user);

    return new Core\DataContainer(
      ['token' => $user->getToken(), 'uid' => $user->getUid()],
      'array'
    );
  }
}
