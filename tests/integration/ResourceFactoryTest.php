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
		return array(
			array('auth', 'Youthweb\Api\Resource\AuthInterface'),
			array('stats', 'Youthweb\Api\Resource\StatsInterface'),
			array('users', 'Youthweb\Api\Resource\UsersInterface'),
		);
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
