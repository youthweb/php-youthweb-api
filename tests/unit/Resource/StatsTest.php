<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Fixtures\MockClient;
use Youthweb\Api\Resource\Stats;
use InvalidArgumentException;

class StatsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testShowAccountReturnsDocumentInterface()
	{
		$document = $this->getMock('\Art4\JsonApiClient\DocumentInterface');

		$client = new MockClient();
		$client->useOriginalGetMethod = false;
		$client->runRequestReturnValue = $document;

		$stats = new Stats($client);

		$response = $stats->show('account');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowForumReturnsObject()
	{
		$document = $this->getMock('\Art4\JsonApiClient\DocumentInterface');

		$client = new MockClient();
		$client->useOriginalGetMethod = false;
		$client->runRequestReturnValue = $document;

		$stats = new Stats($client);

		$response = $stats->show('forum');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowGroupsReturnsObject()
	{
		$document = $this->getMock('\Art4\JsonApiClient\DocumentInterface');

		$client = new MockClient();
		$client->useOriginalGetMethod = false;
		$client->runRequestReturnValue = $document;

		$stats = new Stats($client);

		$response = $stats->show('groups');

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowFoobarThrowsException()
	{
		$client = new MockClient();

		$this->setExpectedException(
			'InvalidArgumentException',
			'The ressource id "foobar" does not exists.'
		);

		$response = $client->getResource('stats')->show('foobar');
	}
}
