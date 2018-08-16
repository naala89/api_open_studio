<?php

namespace Datagator\Admin;

use Datagator\Db;
use Datagator\Core;

class User
{
  private $settings;

  /**
   * User constructor.
   *
   * @param $settings
   */
  public function __construct($settings)
  {
    $this->settings = $settings;
  }

  /**
   * Log a user in.
   *
   * @param $account
   * @param $username
   * @param $password
   *
   * @return bool|string
   */
  public function adminLogin($account, $username, $password)
  {
    $dsnOptions = '';
    if (sizeof($this->settings['db']['options']) > 0) {
      foreach ($this->settings['db']['options'] as $k => $v) {
        $dsnOptions .= sizeof($dsnOptions) == 0 ? '?' : '&';
        $dsnOptions .= "$k=$v";
      }
    }
    $dsnOptions = sizeof($this->settings['db']['options']) > 0 ? '?'.implode('&', $this->settings['db']['options']) : '';
    $dsn = $this->settings['db']['driver'] . '://'
      . $this->settings['db']['username'] . ':'
      . $this->settings['db']['password'] . '@'
      . $this->settings['db']['host'] . '/'
      . $this->settings['db']['database'] . $dsnOptions;
    $db = \ADONewConnection($dsn);

    $accountMapper = new Db\AccountMapper($db);
    $account = $accountMapper->findByName($account);
    if (empty($account->getAccId())) {
      return FALSE;
    }

    $userMapper = new Db\UserMapper($db);
    $user = $userMapper->findByUsername($username);
    if (empty($user->getUid())) {
      return FALSE;
    }

    $userRoleMapper = new Db\UserRoleMapper($db);
    $userRoles = $userRoleMapper->findBy($user->getUid(), NULL, NULL, $account->getAccId());
    if (empty($userRoles)) {
      return FALSE;
    }

    // set up salt if not defined
    if ($user->getSalt() == NULL) {
      $user->setSalt(Core\Hash::generateSalt());
    }

    // generate hash and compare to stored hash this prevents refreshing token with a fake password.
    $hash = Core\Hash::generateHash($password, $user->getSalt());
    if ($user->getHash() != null && $user->getHash() != $hash) {
      return FALSE;
    }

    // if token exists and is active, return it
    if (!empty($user->getToken())
      && !empty($user->getTokenTtl())
      && Core\Utilities::date_mysql2php($user->getTokenTtl()) > time()) {
      $user->setTokenTtl(Core\Utilities::date_php2mysql(strtotime($this->settings['user']['token_life'])));
      return ['token' => $user->getToken(), 'account' => $account->getName(), 'accountId' => $account->getAccId()];
    }

    //perform login
    $user->setHash($hash);
    $token = Core\Hash::generateToken($username);
    $user->setToken($token);
    $user->setTokenTtl(Core\Utilities::date_php2mysql(strtotime($this->settings['user']['token_life'])));
    $userMapper->save($user);

    return ['token' => $token, 'account' => $account->getName(), 'accountId' => $account->getAccId()];
  }

  /**
   * Create a user.
   *
   * @param $username
   * @param $password
   * @param null $email
   * @param null $honorific
   * @param null $nameFirst
   * @param null $nameLast
   * @param null $company
   * @param null $website
   * @param null $addressStreet
   * @param null $addressSuburb
   * @param null $addressCity
   * @param null $addressState
   * @param null $addressPostcode
   * @param null $phoneMobile
   * @param null $phoneWork
   *
   * @return bool|int
   */
  public function create($username, $password, $email=NULL, $honorific=NULL, $nameFirst=NULL, $nameLast=NULL, $company=NULL, $website=NULL, $addressStreet=NULL, $addressSuburb=NULL, $addressCity=NULL, $addressState=NULL, $addressPostcode=NULL, $phoneMobile=NULL, $phoneWork=NULL)
  {
    $user = new Db\User(
      NULL,
      1,
      $username,
      NULL,
      NULL,
      NULL,
      NULL,
      $email,
      $honorific,
      $nameFirst,
      $nameLast,
      $company,
      $website,
      $addressStreet,
      $addressSuburb,
      $addressCity,
      $addressState,
      $addressPostcode,
      $phoneMobile,
      $phoneWork
    );
    $user->setPassword($password);

    $dsnOptions = '';
    if (sizeof($this->settings['db']['options']) > 0) {
      foreach ($this->settings['db']['options'] as $k => $v) {
        $dsnOptions .= sizeof($dsnOptions) == 0 ? '?' : '&';
        $dsnOptions .= "$k=$v";
      }
    }
    $dsnOptions = sizeof($this->settings['db']['options']) > 0 ? '?'.implode('&', $this->settings['db']['options']) : '';
    $dsn = $this->settings['db']['driver'] . '://'
      . $this->settings['db']['username'] . ':'
      . $this->settings['db']['password'] . '@'
      . $this->settings['db']['host'] . '/'
      . $this->settings['db']['database'] . $dsnOptions;
    $db = \ADONewConnection($dsn);

    $userMapper = new Db\UserMapper($db);
    $result = $userMapper->save($user);
    if (!$result) {
      return FALSE;
    }
    $user = $userMapper->findByUsername($username);
    return $user->getUid();
  }
}