<?php

namespace Youthweb\Api;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Http client interface
 */
interface HttpClientInterface
{
	/**
	 * Perform an HTTP request and return response
	 *
	 * @param RequestInterface $request Request to send
	 * @param array            $options Request obtions to apply to the given request and to the transfer
	 *
	 * @throws Exception if request failed (network problem, timeout, etc.)
	 *
	 * @return ResponseInterface
	 */
	public function send(RequestInterface $request, array $options = array());
}
