<?php

namespace Youthweb\Api;

use Psr\Http\Message\RequestInterface;

/**
 * Interface for RequestFactory
 */
interface RequestFactoryInterface
{
	/**
	 * Creates a PSR-7 request instance.
	 *
	 * @param  string $method
	 * @param  string $url
	 * @param  array $headers Headers for the message.
	 * @param  string|resource|StreamInterface $body Message body.
	 * @param  string $version HTTP protocol version.
	 * @return RequestInterface
	 */
	public function createRequest($method, $url, array $headers = [], $body = null, $version = '1.1');
}
