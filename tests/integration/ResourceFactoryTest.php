<?php

namespace Youthweb\Api\Tests\Integration;

use Youthweb\Api\ResourceFactory;

class ResourceFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function testCreateFactory()
    {
        $factory = new ResourceFactory();

        $this->assertInstanceOf('Youthweb\Api\ResourceFactoryInterface', $factory);
    }

    /**
     * @test
     * @dataProvider getResourseClassesProvider
     *
     * @param mixed $resource_name
     * @param mixed $class_name
     */
    public function testCreateResource($resource_name, $class_name)
    {
        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $factory = new ResourceFactory();

        $this->assertInstanceOf(
            $class_name,
            $factory->createResource($resource_name, $client)
        );
    }

    /**
     * Resources DataProvider
     */
    public function getResourseClassesProvider()
    {
        return [
            ['auth', 'Youthweb\Api\Resource\AuthInterface'],
            ['stats', 'Youthweb\Api\Resource\StatsInterface'],
            ['users', 'Youthweb\Api\Resource\UsersInterface'],
        ];
    }

    /**
     * @test
     */
    public function testGetUnknownResourceThrowsException()
    {
        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $factory = new ResourceFactory();

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The resource "foobar" does not exists.');

        $factory->createResource('foobar', $client);
    }
}
