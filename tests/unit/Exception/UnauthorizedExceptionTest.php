<?php

namespace Youthweb\Api\Tests\Unit\Exception;

use Youthweb\Api\Exception\UnauthorizedException;

class UnauthorizedExceptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testUrlGetterAndSetter()
	{
		$url = 'https://example.org';

		$e = new UnauthorizedException;
		$e->setUrl($url);

		$this->assertSame($url, $e->getUrl());
	}

	/**
	 * @test
	 */
	public function testStateGetterAndSetter()
	{
		$state = 'random_string';

		$e = new UnauthorizedException;
		$e->setState($state);

		$this->assertSame($state, $e->getState());
	}

	public function testWithAuthorizationUrl()
	{
		$url = 'https://example.org';
		$state = 'random_string';

		$e = UnauthorizedException::withAuthorizationUrl($url, $state);

		$this->assertInstanceOf(UnauthorizedException::class, $e);

		$this->assertSame($url, $e->getUrl());
		$this->assertSame($state, $e->getState());
	}
}
