<?php

namespace App\Security;

use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use Auth0\SDK\Helpers\JWKFetcher;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Response;
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
}