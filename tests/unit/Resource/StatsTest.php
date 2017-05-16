<?php

namespace Youthweb\Api\Tests\Unit\Resource;

use Youthweb\Api\Resource\Stats;
use InvalidArgumentException;

class StatsTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @test
	 */
	public function testShowAccountReturnsDocumentInterface()
	{
		$document = $this->createMock('\Art4\JsonApiClient\DocumentInterface');

		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$client->expects($this->once())
			->method('getUnauthorized')
			->willReturn($document);

		$stats = new Stats($client);

		$response = $stats->show('account');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowForumReturnsObject()
	{
		$document = $this->createMock('\Art4\JsonApiClient\DocumentInterface');

		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$client->expects($this->once())
			->method('getUnauthorized')
			->willReturn($document);

		$stats = new Stats($client);

		$response = $stats->show('forum');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowGroupsReturnsObject()
	{
		$document = $this->createMock('\Art4\JsonApiClient\DocumentInterface');

		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$client->expects($this->once())
			->method('getUnauthorized')
			->willReturn($document);

		$stats = new Stats($client);

		$response = $stats->show('groups');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowFoobarThrowsException()
	{
		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$stats = new Stats($client);

		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('The ressource id "foobar" does not exists.');

		$response = $stats->show('foobar');
	}
}
