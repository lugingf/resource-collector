<?php

namespace RMT\ResourceCollector;

use Slim\Http\Response;
use Slim\Http\Request;

class HelloTest extends SlimBaseTest
{
    public function testHello()
    {
        /** @var Response $response */
        $response = $this->runApp('GET', '/greeting/Albus');

        $this->assertEquals(200, $response->getStatusCode());

        $response->getBody()->rewind();
        $payload = json_decode($response->getBody()->getContents());

        $this->assertEquals('Hello, Albus', $payload->result);
    }
}
