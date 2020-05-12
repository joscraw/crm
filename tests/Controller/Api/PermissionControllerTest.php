<?php

namespace App\Tests\Controller\Api;

use App\Security\Auth\PermissionManager;
use App\Tests\ApiTestCase;

class PermissionControllerTest extends ApiTestCase
{
    public function testGetRoles() {

        $portal = $this->createPortal();
        $role = $this->createRole('ROLE_SUPER_ADMIN', 'Super Admin Role', null);
        $auth0User = $this->createAuth0User('phpunit@crm.dev', 'phpunit', 'phpunit44!');
        $auth0UserId = $auth0User['user_id'];
        $user = $this->createDbUser('phpunit@crm.dev', 'phpunit', 'phpunit44!', $auth0UserId, [$role]);
        $auth0UserAccessToken = $this->getUserAccessToken('phpunit@crm.dev', 'phpunit44!');


        // todo validate invalid and valid permissions (like if a user doesn't have permissions to hit this endpoint)
        // todo validate that when a role gets added to a portal that this response doesn't show roles
        // todo from different portals when you pass up the portalInternalIdentifier to this endpoint.

        // Make sure the user can't access this endpoint without the proper permissions
        $this->symfonyClient->request('GET', '/api/v1/private/roles', [
            'headers' => [
                'Accept'        => 'application/json'
            ],
        ], [], array(
            'HTTP_AUTHORIZATION' => "Bearer {$auth0UserAccessToken}",
            'CONTENT_TYPE' => 'application/json',
        ));
        $this->assertResponseStatusCodeSame(403);

        // make sure the user can access the endpoint now with proper permissions added.
        $this->createAclEntry(
            sprintf('App\Entity\Role-%s', $role->getId()),
            PermissionManager::MASK_READ,
            'App\Entity\Role'
        );
        $this->symfonyClient->request('GET', '/api/v1/private/roles', [
            'headers' => [
                'Accept'        => 'application/json'
            ],
        ], [], array(
            'HTTP_AUTHORIZATION' => "Bearer {$auth0UserAccessToken}",
            'CONTENT_TYPE' => 'application/json',
        ));
        $this->assertResponseStatusCodeSame(200);

        //var_dump($this->symfonyClient->getResponse()->getContent(true));
    }
}