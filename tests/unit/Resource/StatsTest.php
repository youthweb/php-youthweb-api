<?php

namespace Youthweb\Api\Tests\Resource;

use Youthweb\Api\Fixtures\MockClient;
use InvalidArgumentException;

class StatsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testShowAccountReturnsObject()
	{
		$client = new MockClient();
		$client->useOriginalGetMethod = false;
		$client->runRequestReturnValue = json_decode('{"data":{"type":"stats","id":"account","attributes":{"user_total":5503,"user_total_female":2831, "user_total_male":2672,"user_online":74,"user_online_24h":629,"user_online_7d":1035,"user_online_30d":1600,"userpics":3441}}}', false);

		$response = $client->getResource('stats')->show('account');

		$this->assertTrue(is_object($response));
		$this->assertSame($client->runRequestReturnValue, $response);
	}

	/**
	 * @test
	 */
	public function testShowForumReturnsObject()
	{
		$client = new MockClient();
		$client->useOriginalGetMethod = false;
		$client->runRequestReturnValue = json_decode('{"data":{"type":"stats","id":"forum","attributes":{"authors_total":1480,"threads_total":2094,"posts_total":121387}}}', false);

		$response = $client->getResource('stats')->show('forum');

		$this->assertTrue(is_object($response));
		$this->assertSame($client->runRequestReturnValue, $response);
	}

	/**
	 * @test
	 */
	public function testShowGroupsReturnsObject()
	{
		$client = new MockClient();
		$client->useOriginalGetMethod = false;
		$client->runRequestReturnValue = json_decode('{"data":{"type":"stats","id":"groups","attributes":{"groups_total":614,"users_total":2073}}}', false);

		$response = $client->getResource('stats')->show('groups');

		$this->assertTrue(is_object($response));
		$this->assertSame($client->runRequestReturnValue, $response);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The ressource id "foobar" does not exists.
	 */
	public function testShowFoobarThrowsException()
	{
		$client = new MockClient();

		$response = $client->getResource('stats')->show('foobar');
	}
}
