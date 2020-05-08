<?php

namespace App\Tests;

use App\Entity\Permission;
use App\Entity\Portal;
use App\Entity\Role;
use App\Entity\User;
use App\Security\Auth\PermissionManager;
use App\Security\Auth0MgmtApi;
use App\Security\AuthenticationApi;
use App\Utils\ArrayHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\HttpClient;
use GuzzleHttp\Client;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class ApiTestCase extends WebTestCase
{

    use ArrayHelper;

    /**
     * @var HttpClient
     */
    private static $staticClient;
    private static $staticSymfonyClient;
    private static $staticTestAuth0ApplicationClientId;
    private static $staticTestAuth0ApplicationClientSecret;
    private static $staticTestAuth0ConnectionId;
    private static $staticTestAuth0ApiId;

    /**
     * @var HttpClient
     */
    protected $client;

    protected $symfonyClient;

    /**
     * @var string
     */
    protected $testAuth0ApplicationClientId;

    /**
     * @var string
     */
    protected $testAuth0ApplicationClientSecret;

    /**
     * @var string
     */
    protected $testAuth0ConnectionId;

    /**
     * @var string
     */
    protected $testAuth0ApiId;

    /**
     * @var string
     */
    protected $auth0UserId;

    /**
     * @var string
     */
    protected $auth0UserAccessToken;

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


        self::$staticSymfonyClient = self::createClient([
            'environment' => 'test',
            //'debug'       => false,
        ]);

        //self::bootKernel();

        $response = self::createTestAuth0Application();
        self::$staticTestAuth0ApplicationClientId = $response['client_id'];
        self::$staticTestAuth0ApplicationClientSecret = $response['client_secret'];

        self::updateTestApplicationGrantTypes();

        $response = self::createTestAuth0DatabaseConnection();
        self::$staticTestAuth0ConnectionId = $response['id'];

        $response = self::createTestAuth0Api();
        self::$staticTestAuth0ApiId = $response['id'];

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
        $this->symfonyClient = self::$staticSymfonyClient;
        $this->testAuth0ApplicationClientId = self::$staticTestAuth0ApplicationClientId;
        $this->testAuth0ApplicationClientSecret = self::$staticTestAuth0ApplicationClientSecret;
        $this->testAuth0ApiId = self::$staticTestAuth0ApiId;

        $this->purgeDatabase();

        $this->createPortal();
        $permissions = $this->createPermissions();
        $role = $this->createRole('ROLE_SUPER_ADMIN', 'Super Admin Role', null, $permissions);

        $response = $this->createAuth0User();
        $this->auth0UserId = $response['user_id'];

        $this->createDbUser($this->auth0UserId, [$role]);

        $this->auth0UserAccessToken = $this->getUserAccessToken('phpunit@crm.dev', 'phpunit44!');
    }

    /**
     * Run after each test
     */
    public function tearDown() {
        //$this->deleteAuth0User($this->auth0UserId);
    }

    /**
     * Uses the password grant type to directly retrieve an access token for a
     * given user.
     *
     * @param $username
     * @param $password
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getUserAccessToken($username, $password) {

        //die($username . '-' . $password . '-' . $this->testAuth0ApplicationClientId . '-' . $this->testAuth0ApplicationClientSecret);

        $httpClient = HttpClient::create();

        $data = array(
            'grant_type' => 'http://auth0.com/oauth/grant-type/password-realm',
            'client_id' => $this->testAuth0ApplicationClientId,
            'client_secret' => $this->testAuth0ApplicationClientSecret,
            'username' => $username,
            'password' => $password,
            'scope' => 'openid profile email',
            'audience' => 'https://crm.dev/test-api',
            "realm" => "crm-test-user-pass"
        );

        try {
            $response = $httpClient->request('POST', 'https://crm-development.auth0.com/oauth/token', [
                'json' => $data,
            ]);

        } catch (\Exception $exception) {
            die($exception->getMessage());
        }

        $data = $response->toArray();

        //die(var_dump($data));

        return $data['access_token'];

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

    protected function createUser22(string $email, string $password): User {

        // todo implement this next. You need to create a user in auth0
        // todo and also create a user in the test db as well right?
        // todo we need a generic sign up endpoint right? And a create user endpoint?
        // todo need to think this one through a bit.

    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine.orm.default_entity_manager'));
        $purger->purge();
    }

    /**
     * Uses the real and unchanged service container to fetch services
     *
     * @param $id
     * @return object|null
     */
    protected function getService($id)
    {
        return self::$kernel->getContainer()
            ->get($id);
    }

    /**
     * Uses a special container that allows fetching private services
     * @param $id
     * @return object|null
     */
    protected function getPrivateService($id)
    {
        return self::$container->get($id);
    }

    static public function createTestAuth0Application() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        $data = [
            'name' => 'crm-test',
        ];

        return $auth0MgmtApi->createApplication($data);
    }

    static public function updateTestApplicationGrantTypes() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        $data = [
            'grant_types' => [
                'implicit',
                'authorization_code',
                'refresh_token',
                'client_credentials',
                'password',
                'http://auth0.com/oauth/grant-type/password-realm'
            ],
        ];

        return $auth0MgmtApi->updateApplication(self::$staticTestAuth0ApplicationClientId, $data);
    }

    static public function deleteTestAuth0Application() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        return $auth0MgmtApi->deleteApplication(self::$staticTestAuth0ApplicationClientId);
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

        return $auth0MgmtApi->createConnection($data);
    }

    static public function deleteTestAuth0DatabaseConnection() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->deleteConnection(self::$staticTestAuth0ConnectionId);
    }

    static public function createTestAuth0Api() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $identifier = 'https://crm.dev/test-api';

        $data = [
            'name' => 'crm-test'
        ];

        return $auth0MgmtApi->createApi($identifier, $data);
    }

    static public function deleteTestAuth0Api() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->deleteApi(self::$staticTestAuth0ApiId);
    }

    static public function authorizeTestApplicationToAccessTestApi() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $identifier = 'https://crm.dev/test-api';
        $scopes = ['openid', 'profile', 'email'];
        $auth0MgmtApi->createClientGrant(self::$staticTestAuth0ApplicationClientId, $identifier, $scopes);
    }

    protected function createPortal() {
        $portal = new Portal();
        $portal->setInternalIdentifier('7810945509');
        $portal->setName('PhpUnit Test Portal');
        $portal->setSystemDefined(true);

        $entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $entityManager->persist($portal);
        $entityManager->flush();

        return $portal;
    }

    protected function createAuth0User() {
        $data = [
            'email' => 'phpunit@crm.dev',
            'name' => 'phpunit',
            'password' => 'phpunit44!',
            'connection' => 'crm-test-user-pass'
        ];
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        return $auth0MgmtApi->createUser($data);
    }

    protected function deleteAuth0User($sub) {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        return $auth0MgmtApi->deleteUser($sub);
    }

    protected function createDbUser($sub = null, $roles = []) {
        $user = (new User())
            ->setEmail('phpunit@crm.dev')
            ->setFirstName('phpunit')
            ->setLastName('phpunit');
        if($sub) {
            $user->setSub($sub);
        }
        foreach($roles as $role) {
            $user->addCustomRole($role);
        }
        $entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    protected function createRole($name, $description, $forPortal = null, $permissions = []) {
        $role = new Role();
        $role->setName($name)
            ->setDescription($description);

        if($forPortal and $forPortal instanceof Portal) {
            $role->setPortal($forPortal);
        }

        foreach ($permissions as $permission) {
            $role->addPermission($permission);
        }

        $entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $entityManager->persist($role);
        $entityManager->flush();

        return $role;
    }

    protected function createPermissions() {
        $entityManager = $this->getService('doctrine.orm.default_entity_manager');

        $permissionManager = $this->getPrivateService(PermissionManager::class);
        $permissions = $permissionManager->load();

        $permissionObjs = [];
        foreach($permissions as $key => $permissionSet) {
            foreach($permissionSet as $permission) {
                $permissionObj = new Permission();
                $permissionObj->setScope($permission['scope']);
                $permissionObj->setDescription($permission['description']);
                $entityManager->persist($permissionObj);
                $permissionObjs[] = $permissionObj;
            }
        }

        $entityManager->flush();
        return $permissionObjs;
    }

}