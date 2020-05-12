<?php

namespace App\Tests;

use App\Entity\AclEntry;
use App\Entity\AclSecurityIdentity;
use App\Entity\Permission;
use App\Entity\Portal;
use App\Entity\Role;
use App\Entity\User;
use App\Security\Auth\PermissionManager;
use App\Security\Auth0MgmtApi;
use App\Security\AuthenticationApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp\Client;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class ApiTestCase extends WebTestCase
{

    private static $staticGuzzleClient;
    private static $staticSymfonyClient;

    private static $staticAuth0Application;
    private static $staticAuth0Connection;
    private static $staticAuth0Api;


    protected $guzzleClient;
    protected $symfonyClient;

    protected $auth0User;
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

        self::$staticAuth0Application = self::createAuth0Application(['name' => 'crm-test']);
        self::$staticAuth0Connection = self::createAuth0DatabaseConnection([
            'name' => self::$container->getParameter('auth0_connection'),
            'strategy' => 'auth0',
            'enabled_clients' => [
                self::$staticAuth0Application['client_id']
            ]
        ]);

        self::$staticAuth0Api = self::createAuth0Api(['name' => 'crm-test']);

        self::updateApplicationGrantTypes([
            'grant_types' => [
                'implicit',
                'authorization_code',
                'refresh_token',
                'client_credentials',
                'password',
                'http://auth0.com/oauth/grant-type/password-realm'
            ],
        ]);

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

        $this->purgeDatabase();
    }

    /**
     * Run after each test
     */
    public function tearDown() {
    }

    protected function getApplicationClientId() {
        return self::$staticAuth0Application['client_id'];
    }

    protected function getApplicationClientSecret() {
        return self::$staticAuth0Application['client_secret'];
    }

    protected function getConnectionId() {
        return self::$staticAuth0Connection['id'];
    }

    protected function getApiId() {
        return self::$staticAuth0Api['id'];
    }

    protected function getAuth0UserId() {
        return $this->auth0User['id'];
    }

    /**
     * Uses the password grant type to directly retrieve an access token for a
     * given user.
     *
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getUserAccessToken($username, $password) {
        // todo this should accept a username and password
        // todo so we aren't hardcoding the user we want to get an access token for

        $container = self::$container;
        /** @var AuthenticationApi $authenticationApi */
        $authenticationApi = $container->get(AuthenticationApi::class);

        return $authenticationApi->getAccessToken([
            'grant_type' => 'http://auth0.com/oauth/grant-type/password-realm',
            'client_id' => $this->getApplicationClientId(),
            'client_secret' => $this->getApplicationClientSecret(),
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

        return $this;
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

    static public function createAuth0Application($data) {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        return $auth0MgmtApi->createApplication($data);
    }

    static public function updateApplicationGrantTypes($data) {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        return $auth0MgmtApi->updateApplication(self::$staticAuth0Application['client_id'], $data);
    }

    static public function deleteAuth0Application() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        return $auth0MgmtApi->deleteApplication(self::$staticAuth0Application['client_id']);
    }

    static public function createAuth0DatabaseConnection($data) {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        return $auth0MgmtApi->createConnection($data);
    }

    static public function deleteAuth0DatabaseConnection() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->deleteConnection(self::$staticAuth0Connection['id']);
    }

    static public function createAuth0Api($data) {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);

        return $auth0MgmtApi->createApi(self::$container->getParameter('auth0_audience'), $data);
    }

    static public function deleteAuth0Api() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->deleteApi(self::$staticAuth0Api['id']);
    }

    static public function authorizeApplicationToAccessApi() {
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        $auth0MgmtApi->createClientGrant(
            self::$staticAuth0Application['client_id'],
            self::$container->getParameter('auth0_audience'),
            ['openid', 'profile', 'email']
        );
    }

    protected function createPortal($internalIdentifier = '7810945509', $name = 'PhpUnit Test Portal', $systemDefined = true) {
        $portal = new Portal();
        $portal->setInternalIdentifier($internalIdentifier);
        $portal->setName($name);
        $portal->setSystemDefined($systemDefined);

        $entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $entityManager->persist($portal);
        $entityManager->flush();

        return $portal;
    }

    protected function createAuth0User($email, $name, $password) {
        $data = [
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'connection' => self::$container->getParameter('auth0_connection')
        ];
        $container = self::$container;
        /** @var Auth0MgmtApi $auth0MgmtApi */
        $auth0MgmtApi = $container->get(Auth0MgmtApi::class);
        return $auth0MgmtApi->createUser($data);
    }

    protected function createDbUser($email, $name, $password, $sub = null, $roles = []) {
        $user = (new User())
            ->setEmail($email)
            ->setFirstName($name)
            ->setLastName($password);
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

    protected function createRole($name, $description, $forPortal = null) {
        $role = new Role();
        $role->setName($name)
            ->setDescription($description);

        if($forPortal and $forPortal instanceof Portal) {
            $role->setPortal($forPortal);
        }

        $entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $entityManager->persist($role);
        $entityManager->flush();

        return $role;
    }

    protected function createAclEntry($securityIdentity, $mask, $objectIdentifier = null, $attributeIdentifier = null) {

        if(!$objectIdentifier && !$attributeIdentifier) {
            throw new \Exception("An object identifier or an attribute identifier must be specified.");
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getService('doctrine.orm.default_entity_manager');


        $securityIdentityObj = $entityManager->getRepository(AclSecurityIdentity::class)->findOneBy([
            'identity' => $securityIdentity
        ]);

        if(!$securityIdentityObj) {
            $securityIdentityObj = new AclSecurityIdentity();
            $securityIdentityObj->setIdentity($securityIdentity);
            $entityManager->persist($securityIdentityObj);
        }

        $aclEntry = new AclEntry();
        $aclEntry->setMask($mask);
        $grants = $this->getPrivateService(PermissionManager::class)->resolveGrants($aclEntry);
        $aclEntry->setGrantingStrategy($grants);
        $aclEntry->setObjectIdentifier($objectIdentifier);
        $aclEntry->setAttributeIdentifier($attributeIdentifier);
        $securityIdentityObj->addAclEntry($aclEntry);

        $entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $entityManager->persist($aclEntry);
        $entityManager->flush();
    }
}