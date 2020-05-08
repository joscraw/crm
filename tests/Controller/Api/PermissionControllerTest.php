<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;

class PermissionControllerTest extends ApiTestCase
{
    public function testGetRoles() {

        // todo validate that when a role gets added to a portal that this response doesn't show roles
        // todo from different portals when you pass up the portalInternalIdentifier to this endpoint.

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