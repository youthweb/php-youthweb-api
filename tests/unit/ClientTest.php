<?php

namespace Youthweb\Api\Tests;

use Youthweb\Api\Client;
use InvalidArgumentException;

class ClientTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testSetUrlReturnsClient()
	{
		$client = new Client();

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setUrl('http://test.local'));
	}

	/**
	 * @test
	 */
	public function testGetUrlReturnsValueFromSetUrl()
	{
		$client = (new Client())->setUrl('http://test.local');

		$this->assertSame('http://test.local', $client->getUrl());
	}

	/**
	 * @test
	 */
	public function testSetHttpClientReturnsClient()
	{
		$client = new Client();

		$stub = $this->getMock('Youthweb\Api\HttpClientInterface');

		$this->assertInstanceOf('Youthweb\Api\Client', $client->setHttpClient($stub));
	}

	/**
	 * @test
	 * @dataProvider getResoursesClassesProvider
	 */
	public function testGetApiInstance($resource_name, $class_name)
	{
		$client = new Client();

		$this->assertInstanceOf($class_name, $client->getResource($resource_name));
	}

	public function getResoursesClassesProvider()
	{
		return array(
			array('account', 'Youthweb\Api\Resource\Account'),
			array('stats', 'Youthweb\Api\Resource\Stats'),
		);
	}
}
