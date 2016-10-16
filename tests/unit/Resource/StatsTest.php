<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Resource\Stats;
use InvalidArgumentException;

class StatsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testShowAccountReturnsDocumentInterface()
	{
		$document = $this->getMockBuilder('\Art4\JsonApiClient\DocumentInterface')
			->getMock();

		$client = $this->getMockBuilder('Youthweb\Api\ClientInterface')
			->getMock();

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
		$document = $this->getMockBuilder('\Art4\JsonApiClient\DocumentInterface')
			->getMock();

		$client = $this->getMockBuilder('Youthweb\Api\ClientInterface')
			->getMock();

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
		$document = $this->getMockBuilder('\Art4\JsonApiClient\DocumentInterface')
			->getMock();

		$client = $this->getMockBuilder('Youthweb\Api\ClientInterface')
			->getMock();

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
		$client = $this->getMockBuilder('Youthweb\Api\ClientInterface')
			->getMock();

		$stats = new Stats($client);

		$this->setExpectedException(
			'InvalidArgumentException',
			'The ressource id "foobar" does not exists.'
		);

		$response = $stats->show('foobar');
	}
}
