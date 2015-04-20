<?php

namespace Youthweb\Api;

/**
 * Simple PHP Youthweb client.
 *
 * Website: http://github.com/youthweb/php-youthweb-api
 */
class Client
{
	protected $api_version = '0.1';

	protected $url = 'https://youthweb.net/index.php';

	protected $http_client = null;

	/**
	 * @param string $name
	 *
	 * @return Api\AbstractApi
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getResource($name)
	{
		$classes = array(
			'account' => 'Account',
		);

		if ( ! isset($classes[$name]) )
		{
			throw new \InvalidArgumentException('The ressource "' . $name . '" does not exists.');
		}

		if ( isset($this->resources[$name]) )
		{
			return $this->resources[$name];
		}

		$resource = 'Youthweb\\Api\\Resource\\'.$classes[$name];
		$this->resources[$name] = new $resource($this);

		return $this->resources[$name];
	}

	/**
	 * Returns Url.
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
	 * HTTP GETs a json $path and tries to decode it.
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
	 * @param object $client the http client
	 * @return self
	 */
	public function setHttpClient($client)
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
	 * @throws \Exception If anything goes wrong on curl request
	 */
	protected function runRequest($path, $method = 'GET', array $data = array())
	{
		$client = $this->getHttpClient();

		$request = $client->createRequest($method, $this->getUrl() . $path, array(
			'query' => $data,
		));

		$response = $client->send($request);

		return $response->json();
	}

	/**
	 * Returns the http client
	 *
	 * @return HttpClient The Http client
	 */
	protected function getHttpClient()
	{
		if ( $this->http_client === null )
		{
			$this->setHttpClient(new \GuzzleHttp\Client());
		}

		return $this->http_client;
	}
}
