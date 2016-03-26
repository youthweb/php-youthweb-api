<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Fixtures\MockClient;
use Youthweb\Api\Resource\Auth;
use InvalidArgumentException;

class AuthTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testGetBearerTokenReturnsToken()
	{
		$document = $this->getMockBuilder('Art4\JsonApiClient\DocumentInterface')
			->getMock();

		$document->expects($this->any())
			->method('has')
			->will($this->returnValueMap([
				['meta.token', true],
				['meta.token_type', true],
			]));

		$document->expects($this->any())
			->method('get')
			->will($this->returnValueMap([
				['meta.token', 'JWT'],
				['meta.token_type', 'Bearer'],
			]));

		$client = new MockClient();
		$client->runRequestReturnValue = $document;

		$auth = new Auth($client);

		$this->assertSame('Bearer JWT', $auth->getBearerToken());
	}
}
