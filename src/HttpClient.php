<?php

namespace Youthweb\Api;

use GuzzleHttp;

/**
 * Http client based on GuzzleHttp
 */
class HttpClient implements HttpClientInterface
{
	protected $http_client = null;

	/**
	 * Creates the http client
	 *
	 * @return HttpClient The Http client
	 */
	public function __construct()
	{
		$this->http_client = new GuzzleHttp\Client();
	}

	/**
	 * Send a GET request
		*
	 * @param string|array $url     URL
	 * @param array        $options Array of request options to apply.
		*
	 * @return mixed
	 */
	public function get($url = null, array $options = array())
	{
		$response = $this->http_client->get($url, $options);

		return $response->json(array('object' => true));
	}
}
