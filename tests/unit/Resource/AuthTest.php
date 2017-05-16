<?php

namespace Youthweb\Api\Tests\Unit\Resource;

use Youthweb\Api\Resource\Auth;

class AuthTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @test
	 */
	public function testGetBearerTokenWithoutCredentialsThrowsException()
	{
		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$client->expects($this->exactly(1))
			->method('getUserCredential')
			->will($this->returnValueMap([
				['username', ''],
				['token_secret', ''],
			]));

		$auth = new Auth($client);

		$this->setExpectedException(
			'InvalidArgumentException',
			''
		);

		$auth->getBearerToken();
	}

	/**
	 * @test
	 */
	public function testGetBearerTokenReturnsToken()
	{
		$cache_item = $this->createMock('Psr\Cache\CacheItemInterface');

		$cache_item->expects($this->once())
			->method('isHit')
			->willReturn(false);

		$cache_item->expects($this->once())
			->method('get')
			->willReturn('Bearer JWT');

		$client = $this->createMock('Youthweb\Api\ClientInterface');

		$client->expects($this->exactly(1))
			->method('getCacheItem')
			->willReturn($cache_item);

		$client->expects($this->exactly(4))
			->method('getUserCredential')
			->will($this->returnValueMap([
				['username', 'User'],
				['token_secret', 'secret'],
			]));

		$document = $this->createMock('Art4\JsonApiClient\DocumentInterface');

		$document->expects($this->exactly(2))
			->method('has')
			->will($this->returnValueMap([
				['meta.token', true],
				['meta.token_type', true],
			]));

		$document->expects($this->exactly(2))
			->method('get')
			->will($this->returnValueMap([
				['meta.token', 'JWT'],
				['meta.token_type', 'Bearer'],
			]));

		$client->expects($this->once())
			->method('postUnauthorized')
			->willReturn($document);

		$auth = new Auth($client);

		$this->assertSame('Bearer JWT', $auth->getBearerToken());
	}
}
