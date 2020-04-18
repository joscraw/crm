<?php

namespace App\Service\Auth0;

use App\Service\SessionStore;
use Firebase\JWT\JWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Auth0\SDK\Auth0;
use Auth0\SDK\API\Authentication;

class Authenticator
{

    /**
     * @var string
     */
    private $auth0ClientId;

    /**
     * @var string
     */
    private $auth0ClientSecret;

    /**
     * @var string
     */
    private $auth0Domain;

    /**
     * @var string
     */
    private $auth0Connection;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var Auth0
     */
    private $auth0;

    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var SessionStore
     */
    private $sessionStore;

    /**
     * Auth0Authenticator constructor.
     * @param $auth0ClientId
     * @param $auth0ClientSecret
     * @param $auth0Domain
     * @param $auth0Connection
     * @param UrlGeneratorInterface $router
     * @param SessionStore $sessionStore
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function __construct($auth0ClientId, $auth0ClientSecret, $auth0Domain, $auth0Connection, UrlGeneratorInterface $router, SessionStore $sessionStore)
    {
        /**
         * This is needed probably only on your local dev machine.
         * If your server running the app is in a different timezone or the
         * time is different then auth0's servers you will get an error.
         * This provides extra time leniency between your server and theirs.
         */
        JWT::$leeway = 6000000000000000;

        $this->auth0ClientId = $auth0ClientId;
        $this->auth0ClientSecret = $auth0ClientSecret;
        $this->auth0Domain = $auth0Domain;
        $this->auth0Connection = $auth0Connection;
        $this->router = $router;
        $this->sessionStore = $sessionStore;

        $this->auth0 = new Auth0([
            'audience' => 'https://crm.dev/api',
            'domain' => $auth0Domain,
            'client_id' => $auth0ClientId,
            'client_secret' => $auth0ClientSecret,
            'redirect_uri' => $this->router->generate('auth0_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'scope' => 'openid profile email',
            /*'store' => $this->sessionStore,*/
            'response_type' => 'token id_token', // todo maybe refactor this in the near future to allow picking betweeen "code" and "token" if we might need both.
            'store' => false,
            'response_mode' => 'form_post'
            // todo response mode needs to be set here to something different when using response type token
        ]);

        $this->authentication = new Authentication(
            $auth0Domain,
            $auth0ClientId
        );
    }

    /**
     * @param array $params
     * @return string
     */
    public function getLoginUrl($params = []) {
        $params['connection'] = $this->auth0Connection;
        return $this->auth0->getLoginUrl($params);
    }

    public function logout() {
        $this->auth0->logout();
    }

    /**
     * @return string
     */
    public function getLogoutLink() {
        return $this->authentication->get_logout_link(
            $this->router->generate('logout', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->auth0ClientId,
            true
        );
    }

    /**
     * @return array|null
     * @throws \Auth0\SDK\Exception\ApiException
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function getUser() {
        return $this->auth0->getUser();
    }

    public function login() {
        $this->auth0->login(null, 'NewDBConnection');
    }

}
