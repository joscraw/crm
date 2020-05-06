<?php

namespace App\Security;

use Auth0\SDK\API\Authentication;
use Firebase\JWT\JWT;
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
     * @var string
     */
    private $auth0ManagementClientId;

    /**
     * @var string
     */
    private $auth0ManagementClientSecret;

    /**
     * @var string
     */
    private $auth0ManagementAudience;

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
     * @param string $auth0ManagementClientId
     * @param string $auth0ManagementClientSecret
     * @param string $auth0ManagementAudience
     */
    public function __construct(
        string $auth0ClientId,
        string $auth0ClientSecret,
        string $auth0Domain,
        string $auth0Connection,
        string $auth0Audience,
        string $auth0ManagementClientId,
        string $auth0ManagementClientSecret,
        string $auth0ManagementAudience
    ) {
        $this->auth0ClientId = $auth0ClientId;
        $this->auth0ClientSecret = $auth0ClientSecret;
        $this->auth0Domain = $auth0Domain;
        $this->auth0Connection = $auth0Connection;
        $this->auth0Audience = $auth0Audience;
        $this->auth0ManagementClientId = $auth0ManagementClientId;
        $this->auth0ManagementClientSecret = $auth0ManagementClientSecret;
        $this->auth0ManagementAudience = $auth0ManagementAudience;


        // todo I don't know if they leeway is needed here for the AuthenticationApi
        /**
         * This is needed probably only on your local dev machine.
         * If your server running the app is in a different timezone or the
         * time is different then auth0's servers you will get an error.
         * This provides extra time leniency between your server and theirs.
         */
        JWT::$leeway = 6000000000000000;

        $this->authenticationApi = new Authentication(
            $auth0Domain,
            $auth0ClientId
        );

    }


    /**
     * @return array
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function getUserAccessToken() {

        $config = [
            'client_secret' => $this->auth0ClientSecret,
            'client_id' => $this->auth0ClientId,
            'audience' => $this->auth0Audience,
        ];

        return $this->authenticationApi->client_credentials($config);
    }

    /**
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getManagementApiAccessToken() {

        $cache = new FilesystemAdapter();

        $accessToken = $cache->get('auth0_management_api_access_token', function (ItemInterface $item) {
            // auth0 setting for expiration is 86400 seconds for access tokens issued by the /token endpoint.
            // keep an eye on this if you notice it expiring before this time and just adjust the seconds down here
            $item->expiresAfter(86400);

            $config = [
                'client_secret' => $this->auth0ManagementClientSecret,
                'client_id' => $this->auth0ManagementClientId,
                'audience' => $this->auth0ManagementAudience,
            ];

            $response = $this->authenticationApi->client_credentials($config);

            return $response['access_token'];
        });

        return $accessToken;
    }
}