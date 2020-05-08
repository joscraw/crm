<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testShowPost()
    {
        $client = static::createClient();

        $client->request('GET', '/haha/test');

        die(var_dump($client->getResponse()->getContent()));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}