<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;

class PermissionControllerTest extends ApiTestCase
{
    public function testGetRoles() {

        $this->symfonyClient->request('GET', '/api/v1/private/roles', [
            'headers' => [
                'Accept'        => 'application/json'
            ],
        ], [], array(
            'HTTP_AUTHORIZATION' => "Bearer {$this->auth0UserAccessToken}",
            'CONTENT_TYPE' => 'application/json',
        ));

        $this->assertResponseStatusCodeSame(200);

        //var_dump($this->symfonyClient->getResponse()->getContent(true));
    }
}