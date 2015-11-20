<?php

namespace Youthweb\Api;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Art4\JsonApiClient\Utils\Manager;

/**
 * Simple PHP Youthweb client
 *
 * Website: http://github.com/youthweb/php-youthweb-api
 */
class Client
{
	protected $api_version = '0.3';

	protected $url = 'https://youthweb.net';

	protected $http_client = null;

	/**
	 * @param string $name
	 *
	 * @return Resource\AbstractResource
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getResource($name)
	{
		$classes = array(
			'stats'   => 'Stats',
		);

		if ( ! isset($classes[$name]) )
		{
			throw new \InvalidArgumentException('The resource "' . $name . '" does not exists.');
		}

		if ( ! isset($this->resources[$name]) )
		{
			$resource = 'Youthweb\\Api\\Resource\\'.$classes[$name];
			$this->resources[$name] = new $resource($this);
		}

		return $this->resources[$name];
	}

	/**
	 * Returns the Url
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Set the Url
	 *
	 * @param string $url The url
	 * @return self
	 */
	public function setUrl($url)
	{
		$this->url = (string) $url;

		return $this;
	}

	/**
	 * HTTP GETs a json $path and decodes it to an array
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function get($path, array $data = array())
	{
		return $this->runRequest($path, 'GET', $data);
	}

	/**
	 * Set a http client
	 *
	 * @param HttpClientInterface $client the http client
	 * @return self
	 */
	public function setHttpClient(HttpClientInterface $client)
	{
		$this->http_client = $client;

		return $this;
	}

	/**
	 * @param string $path
	 * @param string $method
	 * @param array  $data
	 *
	 * @return mixed
	 *
	 * @throws \Exception If anything goes wrong on the request
	 */
	protected function runRequest($path, $method = 'GET', array $data = array())
	{
		$headers = [
			'Content-Type' => 'application/vnd.api+json',
			'Accept' => 'application/vnd.api+json, application/vnd.api+json; net.youthweb.api.version=' . $this->api_version,
		];

		$request = new Request($method, $this->getUrl() . $path, $headers);

		$response = $this->getHttpClient()->send($request);

		return $this->parseResponse($response);
	}

	/**
	 * @param Response $response
	 *
	 * @return \Art4\JsonApiClient\Document
	 *
	 * @throws \Exception If anything goes wrong on the request
	 */
	protected function parseResponse(Response $response)
	{
		// 8388608 == 8mb
		$body = $response->getBody()->read(8388608);

		return (new Manager())->parse($body);
	}

	/**
	 * Returns the http client
	 *
	 * @return HttpClientInterface The Http client
	 */
	protected function getHttpClient()
	{
		if ( $this->http_client === null )
		{
			$this->setHttpClient(new HttpClient());
		}

		return $this->http_client;
	}
}
