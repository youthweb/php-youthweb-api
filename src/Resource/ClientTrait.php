<?php

namespace Youthweb\Api\Resource;

use Youthweb\Api\ClientInterface;

/**
 * Trait for Client handling
 */
trait ClientTrait
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
	public function __construct(ClientInterface $client)
	{
		$this->client = $client;
	}
}
