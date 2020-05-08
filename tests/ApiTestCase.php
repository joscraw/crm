<?php

namespace App\Tests;

use App\Entity\Permission;
use App\Entity\Portal;
use App\Entity\Role;
use App\Entity\User;
use App\Security\Auth\PermissionManager;
use App\Security\Auth0MgmtApi;
use App\Security\AuthenticationApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp\Client;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class ApiTestCase extends WebTestCase
{

    private static $staticGuzzleClient;
    private static $staticSymfonyClient;
    private static $staticAuth0ApplicationClientId;
    private static $staticAuth0ApplicationClientSecret;
    private static $staticAuth0ConnectionId;
    private static $staticAuth0ApiId;

    protected $guzzleClient;
    protected $symfonyClient;

    protected $auth0ApplicationClientId;
    protected $auth0ApplicationClientSecret;
    protected $auth0ConnectionId;
    protected $auth0ApiId;
    protected $auth0UserId;
    protected $auth0UserAccessToken;

    /**
     * Instead of being called before every test,
     * this is called once before for the entire class.
     */
    static public function setUpBeforeClass() {

        self::$staticSymfonyClient = self::createClient([
            'environment' => 'test',
            'debug'       => false,
        ]);

        $siteBaseUrl = self::$container->getParameter('site_base_url');

        self::$staticGuzzleClient = new Client([
            'base_uri' => $siteBaseUrl,
            'verify' => false,
            'http_errors' => false
        ]);

        $response = self::createAuth0Application();
        self::$staticAuth0ApplicationClientId = $response['client_id'];
        self::$staticAuth0ApplicationClientSecret = $response['client_secret'];

        self::updateApplicationGrantTypes();

        $response = self::createAuth0DatabaseConnection();
        self::$staticAuth0ConnectionId = $response['id'];

        $response = self::createAuth0Api();
        self::$staticAuth0ApiId = $response['id'];

        self::authorizeApplicationToAccessApi();
    }

    /**
     * Instead of being called after every test,
     * this is called once after for the entire class.
     */
    static public function tearDownAfterClass() {
        self::deleteAuth0Application();
        self::deleteAuth0DatabaseConnection();
        self::deleteAuth0Api();
    }

    /**
     * Run before each test
     */
    public function setUp()
    {
        $this->guzzleClient = self::$staticGuzzleClient;
        $this->symfonyClient = self::$staticSymfonyClient;
        $this->auth0ApplicationClientId = self::$staticAuth0ApplicationClientId;
        $this->auth0ApplicationClientSecret = self::$staticAuth0ApplicationClientSecret;
        $this->auth0ApiId = self::$staticAuth0ApiId;

        $this->purgeDatabase();
        $this->createPortal();
        $permissions = $this->createPermissions();
        $role = $this->createRole('ROLE_SUPER_ADMIN', 'Super Admin Role', null, $permissions);

        $response = $this->createAuth0User();
        $this->auth0UserId = $response['user_id'];

        $this->createDbUser($this->auth0UserId, [$role]);
        $this->auth0UserAccessToken = $this->getUserAccessToken();
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
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getUserAccessToken() {

        $container = self::$container;
        /** @var AuthenticationApi $authenticationApi */
        $authenticationApi = $container->get(AuthenticationApi::class);

        return $authenticationApi->getAccessToken([
            'grant_type' => 'http://auth0.com/oauth/grant-type/password-realm',
            'client_id' => $this->auth0ApplicationClientId,
            'client_secret' => $this->auth0ApplicationClientSecret,
            'username' => 'phpunit@crm.dev',
            'password' => 'phpunit44!',
            'scope' => 'openid profile email',
            'audience' => self::$container->getParameter('auth0_audience'),
            "realm" => self::$container->getParameter('auth0_connection')
        ]);
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

    static public function createAuth0Application() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        $data = [
            'name' => 'crm-test',
        ];

        return $auth0MgmtApi->createApplication($data);
    }

    static public function updateApplicationGrantTypes() {
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

        return $auth0MgmtApi->updateApplication(self::$staticAuth0ApplicationClientId, $data);
    }

    static public function deleteAuth0Application() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        return $auth0MgmtApi->deleteApplication(self::$staticAuth0ApplicationClientId);
    }

    static public function createAuth0DatabaseConnection() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        $data = [
            'name' => self::$container->getParameter('auth0_connection'),
            'strategy' => 'auth0',
            'enabled_clients' => [
                self::$staticAuth0ApplicationClientId
          ]
        ];

        return $auth0MgmtApi->createConnection($data);
    }

    static public function deleteAuth0DatabaseConnection() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->deleteConnection(self::$staticAuth0ConnectionId);
    }

    static public function createAuth0Api() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        return $auth0MgmtApi->createApi(self::$container->getParameter('auth0_audience'), [
            'name' => 'crm-test'
        ]);
    }

    static public function deleteAuth0Api() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->deleteApi(self::$staticAuth0ApiId);
    }

    static public function authorizeApplicationToAccessApi() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->createClientGrant(
            self::$staticAuth0ApplicationClientId,
            self::$container->getParameter('auth0_audience'),
            ['openid', 'profile', 'email']
        );
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
            'connection' => self::$container->getParameter('auth0_connection')
        ];
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        return $auth0MgmtApi->createUser($data);
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