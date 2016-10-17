<?php

namespace Youthweb\Api\Resource;

use Youthweb\Api\ClientInterface;

/**
 * interface for resources.
 */
interface ResourceInterface
{
	/**
	 * @param Client $client
	 */
	public function __construct(ClientInterface $client);
}
