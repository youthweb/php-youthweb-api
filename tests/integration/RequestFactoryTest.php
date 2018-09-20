<?php

namespace Youthweb\Api\Tests\Integration;

use Youthweb\Api\RequestFactory;

class RequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function testCreateFactory()
    {
        $factory = new RequestFactory();

        $this->assertInstanceOf('Youthweb\Api\RequestFactoryInterface', $factory);
    }

    /**
     * @test
     */
    public function testCreateRequest()
    {
        $factory = new RequestFactory();

        $this->assertInstanceOf(
            'Psr\Http\Message\RequestInterface',
            $factory->createRequest('GET', '/foobar')
        );
    }
}
