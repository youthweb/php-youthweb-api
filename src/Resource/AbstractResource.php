<?php

namespace Youthweb\Api\Resource;

use Youthweb\Api\Client;

/**
 * Abstract class for Api resources.
 */
abstract class AbstractResource
{
	/**
	 * The client.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Perform the client get() method.
	 *
	 * @param string $path
	 * @param array  $data
	 *
	 * @return array
	 */
	protected function get($path, array $data = array())
	{
		return $this->client->get($path, $data);
	}
}
