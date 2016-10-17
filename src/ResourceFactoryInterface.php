<?php

namespace Youthweb\Api;

/**
 * Factory for API Resources
 */
interface ResourceFactoryInterface
{
	/**
	 * Creates a API resource
	 *
	 * @param  string $name
	 * @param  ClientInterface $client
	 * @return RequestInterface
	 */
	public function createResource($name, ClientInterface $client);
}
