<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;

class CustomObjectControllerTest extends ApiTestCase
{
    public function testNewCustomObject() {

        self::bootKernel();

        // returns the real and unchanged service container
        /*$container = self::$kernel->getContainer();*/

        // gets the special container that allows fetching private services
        $clientId = self::$container->getParameter('auth0_client_id');

        die($clientId);

        $accessToken = $this->getAccessToken();

       /* echo $accessToken;

        die('test');*/

        //die($accessToken);

        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://crm.dev',
            'verify' => false,
            'http_errors' => false
        ]);

        $data = array(
            'label' => 'test custom object',
            'internalName' => 'test_custom_object'
        );

        // todo fix bugs. It's letting me create custom objects with the same internal name!! ughhh.
        // todo I would honestly say right all your tests first and make sure they don't pass and then go
        // todo fix the code.

        $response = $client->post('/api/v1/private/custom-objects/new?verbosity=fddsds', [
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/json',
            ]
        ]);

        //die($response->getBody()->getContents());
        $body = json_decode($response->getBody(), true);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('label', $body['data']);
        $this->assertArrayHasKey('internalName', $body['data']);
        // todo test location header exists.

        // todo setup test db
        // todo test permissions on this endpoint. test can't create custom objects for even your own portal without proper permissions
        // todo add god status role to the permissions manager so just that one role has to be enabled for you to have complete control and
        //  authorization.

        // todo
        //  TEST missing label
        //  test missing internal name that it gets created.
        //  test validation errors return 404 with message param.
        //  test server errors return 500 with message param.
        //

    }
}