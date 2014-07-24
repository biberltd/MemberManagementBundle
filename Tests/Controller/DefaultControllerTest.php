<?php

namespace BiberLtd\Cores\Bundles\LogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/test/log_bundle');

        $this->assertTrue($crawler->filter('html:contains("Testing Log Bundle.")')->count() > 0);
    }
}
