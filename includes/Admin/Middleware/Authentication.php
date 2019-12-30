<?php

namespace Gaterdata\Admin\Middleware;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Gaterdata\Core\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class Authentication.
 *
 * @package Gaterdata\Admin\Middleware
 */
class Authentication
{

    /**
     * @var Container
     */
    private $settings;
    /**
     * @var string
     */
    private $loginPath;
    /**
     * @var Container
     */
    private $container;

    /**
     * Authentication constructor.
     *
     * @param Container $container
     *   Container.
     * @param array $settings
     *   Application settings.
     * @param string $loginPath
     *   Login URI.
     */
    public function __construct(Container $container, array $settings, $loginPath)
    {
        $this->container = $container;
        $this->settings = $settings;
        $this->loginPath = $loginPath;
    }

    /**
     * Middleware invocation.
     *
     * @param ServerRequestInterface $request
     *   PSR7 request.
     * @param ResponseInterface $response
     *   PSR7 Response.
     * @param callable $next
     *   Next middleware.
     *
     * @return ResponseInterface
     *   Response Interface.
     *
     * @throws GuzzleException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $data = $request->getParsedBody();
        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';

        $domain = $this->settings['api']['url'];
        $account = $this->settings['api']['core_account'];
        $application = $this->settings['api']['core_application'];
        $client = new Client(['base_uri' => "$domain/$account/$application/"]);

        if (!empty($username) || !empty($password)) {
            // This is a login attempt.
            try {
                $result = $client->request('POST', "login", [
                    'form_params' => [
                        'username' => $username,
                        'password' => $password,
                    ]
                ]);
                $result = json_decode($result->getBody()->getContents());
                if (!isset($result->token) || !isset($result->uid)) {
                    return $response->withStatus(302)->withHeader('Location', '/login');
                }
                $_SESSION['token'] = $result->token;
                $_SESSION['uid'] = $result->uid;
                $_SESSION['username'] = $username;
            } catch (BadResponseException $e) {
                $this->container['flash']->addMessage('error', $e->getMessage());
            }
        } else {
            // Validate the token and username.
            try {
                $token = isset($_SESSION['token']) ? $_SESSION['token'] : '';
                $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
                $result = $client->request('GET', "user", [
                    'query' => ['username' => $username],
                    'headers' => ['Authorization' => "Bearer $token"],
                ]);
            } catch (BadResponseException $e) {
                $this->container['flash']->addMessage('error', $e->getMessage());
            }
        }

        // Validate token and uid are set (valid login).
        if (!isset($_SESSION['token']) || !isset($_SESSION['uid']) || !isset($_SESSION['username'])) {
            $loginPath = $request->getUri()->withPath($this->loginPath);
            return $response->withStatus(302)->withHeader('Location', $loginPath);
        }
        return $next($request, $response);
    }
}
