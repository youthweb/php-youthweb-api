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

	/**
	 * Resources DataProvider
	 */
	public function getResoursesClassesProvider()
	{
		return array(
			array('stats', 'Youthweb\Api\Resource\Stats'),
		);
	}

	/**
	 * @test
	 */
	public function testGetUnknownResourceThrowsException()
	{
		$client = new Client();

		$this->setExpectedException(
			'InvalidArgumentException',
			'The resource "foobar" does not exists.'
		);

		$client->getResource('foobar');
	}

	/**
	 * @test
	 */
	public function testParseResponseReturnsObject()
	{
		$body = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
			->disableOriginalConstructor()
			->getMock();

		$body->expects($this->any())
			->method('read')
			->with($this->equalTo('8388608'))
			->willReturn('{"meta":{"this":"that"}}');

		$response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
			->disableOriginalConstructor()
			->getMock();

		$response->expects($this->any())
			->method('getBody')
			->willReturn($body);

		$http_client = $this->getMockBuilder('Youthweb\Api\HttpClientInterface')
			->disableOriginalConstructor()
			->getMock();

		$http_client->expects($this->any())
			->method('send')
			->willReturn($response);

		$client = new Client();
		$client->setHttpClient($http_client);

		$document = $client->get('foobar');

		$this->assertCount(1, get_object_vars($document));
		$this->assertCount(1, get_object_vars($document->meta));
		$this->assertSame('that', $document->meta->this);
	}
}
