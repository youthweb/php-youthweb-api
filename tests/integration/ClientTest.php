<?php

namespace Youthweb\Api\Tests\Integration;

use Youthweb\Api\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testCreateClientWithoutParameters()
	{
		$client = new Client();

		$this->assertInstanceOf('Youthweb\Api\ClientInterface', $client);
	}
}
