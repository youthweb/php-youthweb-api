<?php

namespace Youthweb\Api;

use Art4\JsonApiClient\Utils\Manager;
use Cache\Adapter\Void\VoidCachePool;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Simple PHP Youthweb client
 *
 * Website: http://github.com/youthweb/php-youthweb-api
 */
class Client
{
	protected $api_version = '0.5';

	protected $url = 'https://youthweb.net';

	protected $http_client = null;

	protected $cache_provider = null;

	protected $cache_namespace = 'php_youthweb_api.';

	protected $username = '';

	protected $token_secret = '';

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
			'auth'  => 'Auth',
			'stats' => 'Stats',
			'users' => 'Users',
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
	 * Set the User Credentials
	 *
	 * @param string $username The username
	 * @param string $token_secret The Token-Secret
	 * @return self
	 */
	public function setUserCredentials($username, $token_secret)
	{
		$this->username = strval($username);
		$this->token_secret = strval($token_secret);

		return $this;
	}

	/**
	 * Get a User Credentials
	 *
	 * @param string $key 'username' or 'token_secret'
	 * @return string the requested user credential
	 */
	public function getUserCredential($key)
	{
		$key = strval($key);

		if ( ! in_array($key, ['username', 'token_secret']) )
		{
			throw new \UnexpectedValueException('"' . $key . '" is not a valid key for user credentials.');
		}



		return $this->$key;
	}

	/**
	 * HTTP GETs a json $path and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function get($path, $data = null)
	{
		return $this->runRequest($path, 'GET', $data);
	}

	/**
	 * HTTP GETs a json $path without Authorization and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function getUnauthorized($path, $data = null)
	{
		$config = [
			'authorize' => false,
		];

		return $this->runRequest($path, 'GET', $data, $config);
	}

	/**
	 * HTTP POSTs a json $path without Authorization and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function postUnauthorized($path, $data = null)
	{
		$config = [
			'authorize' => false,
		];

		return $this->runRequest($path, 'POST', $data, $config);
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
	 * Set a cache provider
	 *
	 * @param Psr\Cache\CacheItemPoolInterface $cache_provider the cache provider
	 * @return self
	 */
	public function setCacheProvider(CacheItemPoolInterface $cache_provider)
	{
		$this->cache_provider = $cache_provider;

		return $this;
	}

	/**
	 * Get the cache provider
	 *
	 * @return Psr\Cache\CacheItemPoolInterface the cache provider
	 */
	public function getCacheProvider()
	{
		if ( $this->cache_provider === null )
		{
			$this->setCacheProvider(new VoidCachePool());
		}

		return $this->cache_provider;
	}

	/**
	 * Build a cache key
	 *
	 * @param string $key The key
	 * @return stirng The cache key
	 **/
	public function buildCacheKey($key)
	{
		return $this->cache_namespace . strval($key);
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
	protected function runRequest($path, $method = 'GET', $data = null, array $config = [])
	{
		$default_config = [
			'authorize' => true,
		];

		$config = array_merge($default_config, $config);

		$headers = [
			'Content-Type' => 'application/vnd.api+json',
			'Accept' => 'application/vnd.api+json, application/vnd.api+json; net.youthweb.api.version=' . $this->api_version,
		];

		if ( $config['authorize'] === true )
		{
			$bearer_token = $this->getResource('auth')->getBearerToken();

			$headers['Authorization'] = strval($bearer_token);
		}

		$request = new Request($method, $this->getUrl() . $path, $headers, $data);

		try
		{
			$response = $this->getHttpClient()->send($request);
		}
		catch (\Exception $e)
		{
			throw $this->handleClientException($e);
		}

		return $this->parseResponse($response);
	}

	/**
	 * @param ResponseInterface $response
	 *
	 * @return \Art4\JsonApiClient\Document
	 *
	 * @throws \Exception If anything goes wrong on the request
	 */
	protected function parseResponse(ResponseInterface $response)
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
			$client = new HttpClient([
				// Guzzle Configuration
				//'http_errors' => false,
			]);

			$this->setHttpClient($client);
		}

		return $this->http_client;
	}

	/**
	 * Handels a Exception from the Client
	 *
	 * @param \Exception $e The exception
	 * @return \Exception An exception for re-throwing
	 **/
	protected function handleClientException(\Exception $e)
	{
		$message = null;
		$response = null;

		// Try to get the response
		if ( $e instanceof ClientException or is_callable([$e, 'getResponse']) )
		{
			$response = $e->getResponse();
		}

		if ( is_object($response) and $response instanceof ResponseInterface )
		{
			$document = $this->parseResponse($response);

			// Get an error message from the json api body
			if ( $document->has('errors.0') )
			{
				$error = $document->get('errors.0');

				if ( $error->has('detail') )
				{
					$message = $error->get('detail');
				}
				elseif ( $error->has('title') )
				{
					$message = $error->get('title');
				}
			}
		}

		if ( is_null($message) )
		{
			$message = 'The server responses with an unknown error.';
		}

		return new \Exception($message, $e->getCode(), $e);
	}

	/**
	 * destructor
	 **/
	public function __destruct()
	{
		// Save deferred items
		$this->getCacheProvider()->commit();
	}
}
