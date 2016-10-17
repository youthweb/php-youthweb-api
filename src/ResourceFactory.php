<?php

namespace Youthweb\Api;

/**
 * Factory for API Resources
 */
final class ResourceFactory implements ResourceFactoryInterface
{
	/**
	 * Creates a API resource
	 *
	 * @param  string $name
	 * @param  ClientInterface $client
	 * @return RequestInterface
	 */
	public function createResource($name, ClientInterface $client)
	{
		$classes = array(
			'auth'  => 'Youthweb\\Api\\Resource\\Auth',
			'stats' => 'Youthweb\\Api\\Resource\\Stats',
			'users' => 'Youthweb\\Api\\Resource\\Users',
		);

		if ( ! isset($classes[$name]) )
		{
			throw new \InvalidArgumentException('The resource "' . $name . '" does not exists.');
		}

		$resource = $classes[$name];

		return new $resource($client);
	}
}
