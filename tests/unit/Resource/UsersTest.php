<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Resource\Users;
use InvalidArgumentException;

class UsersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test show()
	 */
	public function testShowUserReturnsDocumentInterface()
	{
		$document = $this->createMock('\Art4\JsonApiClient\DocumentInterface');

		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$client->expects($this->once())
			->method('get')
			->willReturn($document);

		$users = new Users($client);

		$user_id = 123456;

		$this->assertSame($document, $users->show($user_id));
	}

	/**
	 * @test show() with Exception
	 */
	public function testShowFoobarThrowsException()
	{
		$exception = new \Exception('Resource not found', 404);

		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$client->expects($this->any())
			->method('get')
			->will($this->throwException($exception));

		$users = new Users($client);

		$this->setExpectedException(
			'Exception',
			'Resource not found'
		);

		$response = $users->show('invalid_user_id');
	}

	/**
	 * @test showMe()
	 */
	public function testShowMeReturnsDocumentInterface()
	{
		$document = $this->createMock('\Art4\JsonApiClient\DocumentInterface');

		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$client->expects($this->once())
			->method('get')
			->willReturn($document);

		$users = new Users($client);

		$this->assertSame($document, $users->showMe());
	}
}
