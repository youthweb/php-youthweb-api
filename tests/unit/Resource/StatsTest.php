<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Client;
use Youthweb\Api\Resource\Stats;
use InvalidArgumentException;

class StatsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testShowAccountReturnsDocumentInterface()
	{
		$body = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
			->getMock();

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"data":{"type":"stats","id":"account"}}');

		$response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
			->getMock();

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$http_client = $this->getMockBuilder('Youthweb\Api\HttpClientInterface')
			->getMock();

		$http_client->expects($this->once())
			->method('send')
			->willReturn($response);

		$client = new Client();
		$client->setHttpClient($http_client);

		$stats = new Stats($client);

		$response = $stats->show('account');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowForumReturnsObject()
	{
		$body = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
			->getMock();

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"data":{"type":"stats","id":"forum"}}');

		$response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
			->getMock();

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$http_client = $this->getMockBuilder('Youthweb\Api\HttpClientInterface')
			->getMock();

		$http_client->expects($this->once())
			->method('send')
			->willReturn($response);

		$client = new Client();
		$client->setHttpClient($http_client);

		$stats = new Stats($client);

		$response = $stats->show('forum');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowGroupsReturnsObject()
	{
		$body = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
			->getMock();

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"data":{"type":"stats","id":"groups"}}');

		$response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
			->getMock();

		$response->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$http_client = $this->getMockBuilder('Youthweb\Api\HttpClientInterface')
			->getMock();

		$http_client->expects($this->once())
			->method('send')
			->willReturn($response);

		$client = new Client();
		$client->setHttpClient($http_client);

		$stats = new Stats($client);

		$response = $stats->show('groups');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowFoobarThrowsException()
	{
		$client = $this->getMockBuilder('Youthweb\Api\Client')
			->getMock();

		$stats = new Stats($client);

		$this->setExpectedException(
			'InvalidArgumentException',
			'The ressource id "foobar" does not exists.'
		);

		$response = $stats->show('foobar');
	}
}
