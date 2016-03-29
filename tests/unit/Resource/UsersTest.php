<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Fixtures\MockClient;
use Youthweb\Api\Resource\Users;
use InvalidArgumentException;

class UsersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testShowUserReturnsDocumentInterface()
	{
		$document = $this->getMock('\Art4\JsonApiClient\DocumentInterface');

		$client = new MockClient();
		$client->useOriginalGetMethod = false;
		$client->runRequestReturnValue = $document;

		$users = new Users($client);

		$user_id = 123456;

		$response = $users->show($user_id);

		$this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
	}

	/**
	 * @test
	 */
	public function testShowFoobarThrowsException()
	{
		$exception = new \Exception('Resource not found', 404);

		$client = $this->getMockBuilder('Youthweb\Api\Client')
			->disableOriginalConstructor()
			->getMock();

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
}
