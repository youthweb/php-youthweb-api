<?php

namespace Youthweb\Api;

use Art4\JsonApiClient\Utils\Manager as JsonApiClientManager;
use Cache\Adapter\Void\VoidCachePool;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for client
 */
interface ClientInterface
{
	/**
	 * @param string $name
	 *
	 * @return Resource\AbstractResource
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getResource($name);

	/**
	 * Returns the Url
	 *
	 * @deprecated Will be removed in future. Don't use it anymore
	 *
	 * @return string
	 */
	public function getUrl();

	/**
	 * Set the Url
	 *
	 * @deprecated Will be removed in future. Use the constructor instead
	 *
	 * @param string $url The url
	 * @return self
	 */
	public function setUrl($url);

	/**
	 * Set the User Credentials
	 *
	 * @deprecated Since Youthweb-API 0.6
	 *
	 * @param string $username The username
	 * @param string $token_secret The Token-Secret
	 * @return self
	 */
	public function setUserCredentials($username, $token_secret);

	/**
	 * Get a User Credentials
	 *
	 * @deprecated Since Youthweb-API 0.6
	 *
	 * @param string $key 'username' or 'token_secret'
	 * @return string the requested user credential
	 */
	public function getUserCredential($key);

	/**
	 * HTTP GETs a json $path and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function get($path, array $data = []);

	/**
	 * HTTP GETs a json $path without Authorization and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function getUnauthorized($path, array $data = []);

	/**
	 * HTTP POSTs a json $path without Authorization and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function postUnauthorized($path, array $data = []);

	/**
	 * Get the cache provider
	 *
	 * @return Psr\Cache\CacheItemPoolInterface the cache provider
	 */
	public function getCacheProvider();

	/**
	 * Build a cache key
	 *
	 * @param string $key The key
	 * @return stirng The cache key
	 **/
	public function buildCacheKey($key);
}
