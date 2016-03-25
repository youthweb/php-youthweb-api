<?php

namespace Youthweb\Api\Tests;

use Youthweb\Api\JsonObject;

class JsonObjectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testToStringReturnsJson()
	{
		$class = new JsonObject;

		$this->assertSame('{}', strval($class));
	}
}
