<?php

namespace App\Security;

use Auth0\SDK\API\Authentication;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class AuthenticationApi
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
     * @var string
     */
    private $auth0Audience;

    /**
     * @var Authentication
     */
    private $authenticationApi;

    /**
     * AuthenticationApi constructor.
     * @param string $auth0ClientId
     * @param string $auth0ClientSecret
     * @param string $auth0Domain
     * @param string $auth0Connection
     * @param $auth0Audience
     */
    public function __construct(
        string $auth0ClientId,
        string $auth0ClientSecret,
        string $auth0Domain,
        string $auth0Connection,
        string $auth0Audience
    ) {
        $this->auth0ClientId = $auth0ClientId;
        $this->auth0ClientSecret = $auth0ClientSecret;
        $this->auth0Domain = $auth0Domain;
        $this->auth0Connection = $auth0Connection;
        $this->auth0Audience = $auth0Audience;

        $this->authenticationApi = new Authentication(
            $auth0Domain,
            $auth0ClientId
        );

    }

    /**
     * @param $data
     * @param bool $fullResponse
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAccessToken($data, $fullResponse = false) {

        $cache = new FilesystemAdapter();

        $config = array_merge($config = array(
            'client_id' => $this->auth0ClientId,
            'client_secret' => $this->auth0ClientSecret,
            'grant_type' => 'client_credentials',
            'audience' => $this->auth0Audience
        ), $data);

        $key = md5(json_encode($config)) . '_auth0_access_token';

        $response = $cache->get($key, function (ItemInterface $item) use($config) {
            // auth0 setting for expiration is 86400 seconds for access tokens issued by the /token endpoint.
            // keep an eye on this if you notice it expiring before this time and just adjust the seconds down here
            $item->expiresAfter(86400);

            return $this->authenticationApi->oauth_token($config);
        });

        if($fullResponse) {
            return $response;
        }

        return $response['access_token'];
    }
}