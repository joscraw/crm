<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class ApiTestCase extends WebTestCase
{

    /**
     * Uses the password grant type to directly retrieve an access token for a
     * given user.
     *
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getUserAccessToken() {

        $cache = new FilesystemAdapter();

        $accessToken = $cache->get('auth0_access_token', function (ItemInterface $item) {
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
        });

        return $accessToken;

    }

    /**
     * Uses the client credentials grant type to just use a client id and
     * client secret for authentication into the management api.
     *
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getMachineToMachineAccessToken() {

        $cache = new FilesystemAdapter();

        $accessToken = $cache->get('auth0_access_token', function (ItemInterface $item) {
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
        });

        return $accessToken;

    }

    // todo need logic to create a generic access token not associated with a user
    // todo need a test auth0 application in the dev tenant
    // todo need a test db connection attached to it in the dev tenant
    // todo need an api attached to the application in auth0
    // todo need the endpoint to create the user as well.
    // todo then you can add this user to the test db so requests can authorize
    // todo with that user. You need tear down logic as well to remove the
    // todo application and db in auth0. You will need basic client credentials
    // todo for making the requests. You need to be able to access the auth0 api in
    // todo your test env. How do you inject services here? You need to add auth0
    // todo client id and stuff in the .env.test file as well. Do you get these
    // todo from the response from auth0 after creating the application?
    // todo need to build the logic for creating a user in auth0
    // todo need to build in the logic to create user/portal

    protected function createUser(string $email, string $password): User {
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
}