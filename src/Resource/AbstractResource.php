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
	protected function get($path, $data = null)
	{
		return $this->client->get($path, $data);
	}

	/**
	 * Perform the client get() method without Authorization.
	 *
	 * @param string $path
	 * @param array  $data
	 *
	 * @return array
	 */
	protected function getUnauthorized($path, $data = null)
	{
		return $this->client->getUnauthorized($path, $data);
	}

	/**
	 * Perform the client post() method without Authorization.
	 *
	 * @param string $path
	 * @param array  $data
	 *
	 * @return array
	 */
	protected function postUnauthorized($path, $data = null)
	{
		return $this->client->postUnauthorized($path, $data);
	}
}
