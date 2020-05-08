<?php

namespace App\Security;

use Auth0\SDK\Helpers\JWKFetcher;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;
use Firebase\JWT\JWT;

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
     * Auth0Authenticator constructor.
     * @param $auth0ClientId
     * @param $auth0ClientSecret
     * @param $auth0Domain
     * @param $auth0Connection
     * @param $auth0Audience
     */
    public function __construct($auth0ClientId, $auth0ClientSecret, $auth0Domain, $auth0Connection, $auth0Audience)
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