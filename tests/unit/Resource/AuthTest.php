<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Client;
use Youthweb\Api\HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Youthweb\Api\Resource\Auth;
use InvalidArgumentException;

class AuthTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testGetBearerTokenReturnsToken()
	{
		$body = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
			->getMock();

		$body->expects($this->once())
			->method('getContents')
			->willReturn('{"meta":{"token_type":"Bearer","token":"JWT"}}');

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

		$auth = new Auth($client);

		$this->assertSame('Bearer JWT', $auth->getBearerToken());
	}
}
