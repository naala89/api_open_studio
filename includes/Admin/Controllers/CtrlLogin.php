<?php

namespace Datagator\Admin\Controllers;

/**
 * Class Login
 * @package Datagator\Admin\Controllers
 */
class CtrlLogin extends CtrlBase
{
  /**
   * Login page controller.
   *
   * @param $request
   *   Request object.
   * @param $response
   *   Response object.
   * @param $args
   *   Request args,
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  public function login($request, $response, $args) {
    $title = 'Login';
    $menu = $this->getMenus([]);
    return $this->view->render($response, 'login.twig', ['menu' => $menu, 'title' => $title]);
  }

  /**
   * Logout page controller.
   *
   * @param $request
   *   Request object.
   * @param $response
   *   Response object.
   * @param $args
   *   Request args,
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  public function logout($request, $response, $args) {
    $title = 'Login';
    $menu = $this->getMenus([]);
    unset($_SESSION['token']);
    unset($_SESSION['accountName']);
    unset($_SESSION['accountId']);
    return $this->view->render($response, 'login.twig', ['menu' => $menu, 'title' => $title]);
  }

}
