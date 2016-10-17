<?php

namespace Youthweb\Api;

use GuzzleHttp\Psr7\Request;

/**
 * Factory for PSR-7 Requests
 */
final class RequestFactory implements RequestFactoryInterface
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
	public function createRequest($method, $url, array $headers = [], $body = null, $version = '1.1')
	{
		return new Request($method, $url, $headers, $body, $version);
	}
}
