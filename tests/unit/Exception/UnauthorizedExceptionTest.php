<?php

namespace Youthweb\Api\Tests\Exception;

use Youthweb\Api\Exception\UnauthorizedException;

class UnauthorizedExceptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testWithAuthorizationUrl()
	{
		$url = 'https://example.org';

		$e = UnauthorizedException::withAuthorizationUrl($url);

		$this->assertInstanceOf(UnauthorizedException::class, $e);

		$this->assertSame($url, $e->getUrl());
	}
}
