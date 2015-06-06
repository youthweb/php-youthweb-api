<?php

namespace Youthweb\Api;

/**
 * Simple PHP Youthweb client
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
	 * @return Resource\AbstractResource
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getResource($name)
	{
		$classes = array(
			'account' => 'Account',
			'stats'   => 'Stats',
		);

		if ( ! isset($classes[$name]) )
		{
			throw new \InvalidArgumentException('The ressource "' . $name . '" does not exists.');
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
	 * @throws \InvalidArgumentException If the method is not supported
	 */
	protected function runRequest($path, $method = 'GET', array $data = array())
	{
		$methods = array(
			'GET' => 'get',
		);

		if ( ! isset($methods[$method]) )
		{
			throw new \InvalidArgumentException('The method "' . $method . '" is not supported.');
		}

		$method = $methods[$method];

		return $this->getHttpClient()
			->$method($this->getUrl() . $path, array('query' => $data));
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
