<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Fixtures\MockClient;
use InvalidArgumentException;

class AccountTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testStatsReturnsArray()
	{
		$client = new MockClient();
		$client->useOriginalGetMethod = false;
		$client->runRequestReturnValue = json_decode('{"user_total":5503,"user_online":74}', false);

		$response = $client->getResource('account')->stats();

		$this->assertTrue(is_array($response));
		$this->assertSame(array('user_total' => 5503, 'user_online' => 74), $response);
	}
}
