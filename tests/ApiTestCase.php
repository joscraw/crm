<?php

namespace App\Tests;

use App\Security\Auth0MgmtApi;
use App\Security\Auth0Service;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use GuzzleHttp\Client;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class ApiTestCase extends WebTestCase
{

    /**
     * @var HttpClient
     */
    private static $staticClient;
    private static $staticUserAccessToken;
    private static $staticMachineAccessToken;
    private static $staticTestAuth0ApplicationClientId;
    private static $staticTestAuth0ConnectionId;
    private static $staticTestAuth0ApiId;

    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $userAccessToken;

    /**
     * @var string
     */
    protected $machineAccessToken;

    /**
     * @var string
     */
    protected $testAuth0ApplicationClientId;

    /**
     * @var string
     */
    protected $testAuth0ConnectionId;

    /**
     * @var string
     */
    protected $testAuth0ApiId;

    /**
     * Instead of being called before every test,
     * this is called once before for the entire class.
     */
    static public function setUpBeforeClass() {

        // todo, you need to pull the base_uri from the env incase someone
        //  sets this app up on their local and doesn't use the same local domain
        self::$staticClient = new Client([
            'base_uri' => 'https://crm.dev',
            'verify' => false,
            'http_errors' => false
        ]);

        self::bootKernel();

        self::$staticMachineAccessToken = self::getMachineToMachineAccessToken();
        self::$staticUserAccessToken = self::getUserAccessToken();
        self::$staticTestAuth0ApplicationClientId = self::createTestAuth0Application();
        self::$staticTestAuth0ConnectionId = self::createTestAuth0DatabaseConnection();
        self::$staticTestAuth0ApiId = self::createTestAuth0Api();
        self::authorizeTestApplicationToAccessTestApi();

    }

    /**
     * Instead of being called after every test,
     * this is called once after for the entire class.
     */
    static public function tearDownAfterClass() {
        self::deleteTestAuth0Application();
        self::deleteTestAuth0DatabaseConnection();
        self::deleteTestAuth0Api();
    }

    /**
     * Run before each test
     */
    public function setUp()
    {
        $this->client = self::$staticClient;
        $this->machineAccessToken = self::$staticMachineAccessToken;
        $this->userAccessToken = self::$staticUserAccessToken;
        $this->testAuth0ApplicationClientId = self::$staticTestAuth0ApplicationClientId;
        $this->testAuth0ApiId = self::$staticTestAuth0ApiId;

        $this->purgeDatabase();
    }

    /**
     * Run after each test
     */
    public function tearDown() {
    }

    /**
     * Uses the password grant type to directly retrieve an access token for a
     * given user.
     *
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    static public function getUserAccessToken() {

        $cache = new FilesystemAdapter();

/*        $accessToken = $cache->get('auth0_user_access_token', function (ItemInterface $item) {
            // auth0 setting for expiration is 86400 seconds for access tokens issued by the /token endpoint.
            // keep an eye on this if you notice it expiring before this time and just adjust the seconds down here
            $item->expiresAfter(86400);

            $httpClient = HttpClient::create();

            $data = array(
                'grant_type' => 'password',
                'client_id' => 'Hhzjj4oe1CuKYcd9C0nbSjh5ltScR5oL',
                'client_secret' => 'IYAPxnyXDlApji5FyIJ_QloJXzY938veaI2YuYaq8-QOyNzt8wCPP7noi0fkAmqX',
                'username' => 'joshcrawmer4@yahoo.com',
                'password' => 'Iluv2rap!',
                'scope' => 'openid profile email',
                'audience' => 'https://crm.dev/api'
            );

            $response = $httpClient->request('POST', 'https://crm-development.auth0.com/oauth/token', [
                'json' => $data,
            ]);

            $data = $response->toArray();

            return $data['access_token'];
        });*/

        $httpClient = HttpClient::create();

        // todo pull from .env file if you even need to anymore cause you can use the mgmt service
        // todo you need to get this access token after a user is created instead of before.
        // todo you need to use the client id and secret generated from the test application setup
        $data = array(
            'grant_type' => 'password',
            'client_id' => 'Hhzjj4oe1CuKYcd9C0nbSjh5ltScR5oL',
            'client_secret' => 'IYAPxnyXDlApji5FyIJ_QloJXzY938veaI2YuYaq8-QOyNzt8wCPP7noi0fkAmqX',
            'username' => 'joshcrawmer4@yahoo.com',
            'password' => 'Iluv2rap!',
            'scope' => 'openid profile email',
            'audience' => 'https://crm.dev/api'
        );

        $response = $httpClient->request('POST', 'https://crm-development.auth0.com/oauth/token', [
            'json' => $data,
        ]);

        $data = $response->toArray();

        return $data['access_token'];

       // return $accessToken;

    }

    /**
     * Uses the client credentials grant type to just use a client id and
     * client secret for authentication into the management api.
     *
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    static public function getMachineToMachineAccessToken() {

        // todo re-add the cache. But you need to figure out how to actually
        // todo clear this cache somehow. Cause it was caching old access tokens without
        // todo the proper permissions yet.
        // todo machine to machine client id and secret will be shared across all apps
        // todo for each tenant. so you essentially will need to create 2 more. 1 for staging
        // todo and production.
/*        $cache = new FilesystemAdapter();

        $accessToken = $cache->get('auth0_machine_access_token', function (ItemInterface $item) {
            // auth0 setting for expiration is 86400 seconds for access tokens issued by the /token endpoint.
            // keep an eye on this if you notice it expiring before this time and just adjust the seconds down here
            $item->expiresAfter(86400);

            $httpClient = HttpClient::create();

            $data = array(
                'grant_type' => 'client_credentials',
                'client_id' => 'CVZvis3P0FWa7BxoCCA6rrphMzqlodTS',
                'client_secret' => 'XgpO2H7rIFNYkrzKZK4EkbY338J4sTmW6g72AED8bopxC5RrSodo7Ta87plvaisZ',
                'audience' => 'https://crm-development.auth0.com/api/v2/'
            );

            $response = $httpClient->request('POST', 'https://crm-development.auth0.com/oauth/token', [
                'json' => $data,
            ]);

            $data = $response->toArray();

            return $data['access_token'];
        });*/

        $httpClient = HttpClient::create();

        // todo pull from .env file if you even need to anymore cause you can use the mgmt service
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => 'CVZvis3P0FWa7BxoCCA6rrphMzqlodTS',
            'client_secret' => 'XgpO2H7rIFNYkrzKZK4EkbY338J4sTmW6g72AED8bopxC5RrSodo7Ta87plvaisZ',
            'audience' => 'https://crm-development.auth0.com/api/v2/'
        );

        $response = $httpClient->request('POST', 'https://crm-development.auth0.com/oauth/token', [
            'json' => $data,
        ]);

        $data = $response->toArray();

        die($data['access_token']);
        return $data['access_token'];




        //return $accessToken;

    }


    // todo need the endpoint to create the user as well.
    // todo then you can add this user to the test db so requests can authorize
    // todo with that user. You need tear down logic as well to remove the
    // todo application and db in auth0. You will need basic client credentials
    // todo for making the requests. You need to be able to access the auth0 api in
    // todo client id and stuff in the .env.test file as well. Do you get these
    // todo from the response from auth0 after creating the application?
    // todo need to build the logic for creating a user in auth0
    // todo need to build in the logic to create user/portal

    protected function createUser(string $email, string $password): User {

        // todo implement this next. You need to create a user in auth0
        // todo and also create a user in the test db as well right?
        // todo we need a generic sign up endpoint right? And a create user endpoint?
        // todo need to think this one through a bit.
        /*$user = new User();
        $user->setEmail($email);
        $user->setUsername(substr($email, 0, strpos($email, '@')));

        $encoded = self::$container->get('security.password_encoder')
            ->encodePassword($user, $password);
        $user->setPassword($encoded);

        $em = self::$container->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $user;*/
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine.orm.default_entity_manager'));
        $purger->purge();
    }

    protected function getService($id)
    {
        return self::$kernel->getContainer()
            ->get($id);
    }

    static public function createTestAuth0Application() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        $data = [
            'name' => 'crm-test',
        ];

        $response = $auth0MgmtApi->createApplication(self::$staticMachineAccessToken, $data);
        return $response['client_id'];
    }

    static public function deleteTestAuth0Application() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        return $auth0MgmtApi->deleteApplication(self::$staticMachineAccessToken, self::$staticTestAuth0ApplicationClientId);
    }

    static public function createTestAuth0DatabaseConnection() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        $data = [
            'name' => 'crm-test-user-pass',
            'strategy' => 'auth0',
            'enabled_clients' => [
                self::$staticTestAuth0ApplicationClientId
                // todo add auth0 management application client id here as well
          ]
        ];

        $response = $auth0MgmtApi->createConnection(self::$staticMachineAccessToken, $data);
        return $response['id'];
    }

    static public function deleteTestAuth0DatabaseConnection() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->deleteConnection(self::$staticMachineAccessToken, self::$staticTestAuth0ConnectionId);
    }

    static public function createTestAuth0Api() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        // todo refactor all these static strings into the env file
        $identifier = 'https://crm.dev/test-api';

        $data = [
            'name' => 'crm-test'
        ];

        $response = $auth0MgmtApi->createApi(self::$staticMachineAccessToken, $identifier, $data);
        return $response['id'];
    }

    static public function deleteTestAuth0Api() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->deleteApi(self::$staticMachineAccessToken, self::$staticTestAuth0ApiId);
    }

    static public function authorizeTestApplicationToAccessTestApi() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $identifier = 'https://crm.dev/test-api';
        $auth0MgmtApi->createClientGrant(self::$staticMachineAccessToken, self::$staticTestAuth0ApplicationClientId, $identifier);
    }
}