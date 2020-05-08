<?php

namespace App\Security;

use Firebase\JWT\JWT;
use Auth0\SDK\API\Management;

class Auth0MgmtApi
{
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
    private $auth0Domain;

    /**
     * @var string
     */
    private $auth0Connection;

    /**
     * @var string
     */
    private $auth0ManagementAudience;

    /**
     * @var AuthenticationApi
     */
    private $authenticationApi;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * Auth0MgmtApi constructor.
     * @param string $auth0ManagementClientId
     * @param string $auth0ManagementClientSecret
     * @param string $auth0Domain
     * @param string $auth0Connection
     * @param string $auth0ManagementAudience
     * @param AuthenticationApi $authenticationApi
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct(
        string $auth0ManagementClientId,
        string $auth0ManagementClientSecret,
        string $auth0Domain,
        string $auth0Connection,
        string $auth0ManagementAudience,
        AuthenticationApi $authenticationApi
    ) {

        /**
         * This is needed probably only on your local dev machine.
         * If your server running the app is in a different timezone or the
         * time is different then auth0's servers you will get an error.
         * This provides extra time leniency between your server and theirs.
         */
        JWT::$leeway = 6000000000000000;

        $this->auth0ManagementClientId = $auth0ManagementClientId;
        $this->auth0ManagementClientSecret = $auth0ManagementClientSecret;
        $this->auth0Domain = $auth0Domain;
        $this->auth0Connection = $auth0Connection;
        $this->auth0ManagementAudience = $auth0ManagementAudience;
        $this->authenticationApi = $authenticationApi;

        $this->accessToken = $this->authenticationApi->getManagementApiAccessToken();
    }

    /**
     * @param array $data
     * @return mixed|string
     * @throws \Exception
     */
    public function createApplication($data = []) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        // default to non_interactive app_type (machine to machine)
        $data = array_merge([
            'app_type' => 'non_interactive'

        ], $data);

        return $mgmtApi->clients()->create($data);
    }

    /**
     * @param $clientId
     * @param array $data
     * @return mixed|string
     * @throws \Exception
     */
    public function updateApplication($clientId, $data = []) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        return $mgmtApi->clients()->update($clientId, $data);
    }

    /**
     * @param $clientId
     * @return mixed|string
     * @throws \Exception
     */
    public function deleteApplication($clientId) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);
        return $mgmtApi->clients()->delete($clientId);
    }

    /**
     * @param array $data
     * @return mixed|string
     * @throws \Exception
     */
    public function createConnection($data = []) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        // default to auth0 strategy (standard db connection)
        $data = array_merge([
            'strategy' => 'auth0',
            'enabled_clients' => []
        ], $data);

        // make sure we are always adding the auth0 management clientId
        // so the auth0 management application/api has access to every connection created
        $data['enabled_clients'][] = $this->auth0ManagementClientId;


        return $mgmtApi->connections()->create($data);
    }

    /**
     * @param $connectionId
     * @return mixed|string
     * @throws \Exception
     */
    public function deleteConnection($connectionId) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        return $mgmtApi->connections()->delete($connectionId);
    }

    /**
     * @param $identifier
     * @param array $data
     * @return mixed
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function createApi($identifier, $data = []) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        $data = array_merge([
        ], $data);


        return $mgmtApi->resourceServers()->create($identifier, $data);
    }

    /**
     * @param $apiId
     * @return mixed
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function deleteApi($apiId) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        return $mgmtApi->resourceServers()->delete($apiId);
    }

    /**
     * @param $clientId
     * @param $audience
     * @param array $scopes
     * @return mixed
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function createClientGrant($clientId, $audience, $scopes = []) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        return $mgmtApi->clientGrants()->create($clientId, $audience, $scopes);
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function createUser($data) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        $data = array_merge([
            'connection' => $this->auth0Connection
        ], $data);

        return $mgmtApi->users()->create($data);
    }

    /**
     * @param $sub
     * @return mixed
     * @throws \Exception
     */
    public function deleteUser($sub) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        return $mgmtApi->users()->delete($sub);
    }

    /**
     * Search by email along with additional parameters as well.
     * @param $email
     * @param array $data
     * @return mixed
     */
    public function searchUsersByEmail($email, $data = []) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        $data['email'] = $email;

        return $mgmtApi->usersByEmail()->get($data);
    }

    public function createRole($name, $data = []) {

        $mgmtApi = new Management($this->accessToken, $this->auth0Domain);

        return $mgmtApi->roles()->create($name, $data);

    }
}