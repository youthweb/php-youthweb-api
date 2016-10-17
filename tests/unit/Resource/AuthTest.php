<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Resource\Auth;

class AuthTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testGetBearerTokenReturnsToken()
	{
		$cache_item = $this->getMockBuilder('Psr\Cache\CacheItemInterface')
			->getMock();

		$cache_item->expects($this->once())
			->method('isHit')
			->willReturn(false);

		$cache_item->expects($this->once())
			->method('get')
			->willReturn('Bearer JWT');

		$cache_pool = $this->getMockBuilder('Psr\Cache\CacheItemPoolInterface')
			->getMock();

		$cache_pool->expects($this->once())
			->method('getItem')
			->with('cache_item_key')
			->willReturn($cache_item);

		$client = $this->getMockBuilder('Youthweb\Api\ClientInterface')
			->getMock();

		$client->expects($this->once())
			->method('buildCacheKey')
			->willReturn('cache_item_key');

		$client->expects($this->exactly(2))
			->method('getCacheProvider')
			->willReturn($cache_pool);

		$client->expects($this->exactly(2))
			->method('getUserCredential')
			->will($this->returnValueMap([
				['username', 'User'],
				['token_secret', 'secret'],
			]));

		$document = $this->getMockBuilder('Art4\JsonApiClient\DocumentInterface')
			->getMock();

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
