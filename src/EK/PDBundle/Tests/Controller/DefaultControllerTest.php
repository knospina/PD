<?php

namespace EK\PDBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/1');

        $this->assertTrue($crawler->filter('html:contains("Evita Knospiņa")')->count() > 0);
    }
}
