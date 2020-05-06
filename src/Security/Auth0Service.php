<?php

namespace App\Security;

use Auth0\SDK\Helpers\JWKFetcher;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;
use Firebase\JWT\JWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Auth0\SDK\Auth0;
use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;

class Auth0Service
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
     * @var
     */
    private $auth0Audience;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var Auth0
     */
    private $auth0Api;

    /**
     * @var Authentication
     */
    private $authenticationApi;

    /**
     * Auth0Authenticator constructor.
     * @param $auth0ClientId
     * @param $auth0ClientSecret
     * @param $auth0Domain
     * @param $auth0Connection
     * @param $auth0Audience
     * @param UrlGeneratorInterface $router
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function __construct($auth0ClientId, $auth0ClientSecret, $auth0Domain, $auth0Connection, $auth0Audience, UrlGeneratorInterface $router)
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
        $this->auth0Audience = $auth0Audience;
        $this->router = $router;

        $this->auth0Api = new Auth0([
            'audience' => $this->auth0Audience,
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

        $this->authenticationApi = new Authentication(
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
        return $this->auth0Api->getLoginUrl($params);
    }

    public function logout() {
        $this->auth0Api->logout();
    }

    /**
     * @return string
     */
    public function getLogoutLink() {
        return $this->authenticationApi->get_logout_link(
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
        return $this->auth0Api->getUser();
    }

    /**
     * @param $jwt
     * @return array
     */
    public function getUserProfileByJwt($jwt)
    {
        return $this->authenticationApi->userinfo($jwt);
    }

    /**
     * @param $jwt
     * @param $sub
     * @return mixed
     * @throws \Exception
     */
    public function getUserBySub($jwt, $sub) {
        $mgmtApi = new Management($jwt, $this->auth0Domain);
        return $mgmtApi->users()->get($sub);
    }

    /**
     * Decodes the JWT and validate it
     * @param $encToken
     * @return array
     * @throws \Auth0\SDK\Exception\InvalidTokenException
     */
    public function decodeJWT($encToken)
    {
        $issuer  = 'https://'.$this->auth0Domain.'/';
        $jwksUri      = $issuer . '.well-known/jwks.json';
        $jwksFetcher   = new JWKFetcher(null, [ 'base_uri' => $jwksUri ]);
        $sigVerifier   = new AsymmetricVerifier($jwksFetcher);
        $tokenVerifier = new TokenVerifier($issuer, $this->auth0Audience, $sigVerifier);
        return $tokenVerifier->verify($encToken);
    }

    /**
     * @return array
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function getAccessToken() {

        $config = [
            'client_secret' => $this->auth0ClientSecret,
            'client_id' => $this->auth0ClientId,
            'audience' => $this->auth0Audience,
        ];

        return $this->authenticationApi->client_credentials($config);
    }

    public function createClient($accessToken) {

        $mgmtApi = new Management($accessToken, $this->auth0Domain);

        $data = [
            'name' => 'crm-test',
            'app_type' => 'Machine To Machine'

        ];

        return $mgmtApi->clients()->create($data);

    }
}