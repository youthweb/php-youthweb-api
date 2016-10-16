<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Client;
use Youthweb\Api\Resource\Users;
use InvalidArgumentException;

class UsersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testShowUserReturnsDocumentInterface()
	{
		$body = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
			->getMock();

		$body->expects($this->exactly(2))
			->method('getContents')
			->willReturn('{"data":{"type":"users","id":"123456"}}');

		$response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
			->getMock();

		$response->expects($this->exactly(2))
			->method('getBody')
			->willReturn($body);

		$http_client = $this->getMockBuilder('Youthweb\Api\HttpClientInterface')
			->getMock();

		$http_client->expects($this->exactly(2))
			->method('send')
			->willReturn($response);

		$client = new Client();
		$client->setHttpClient($http_client);

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
