<?php

namespace Youthweb\Api;

use Art4\JsonApiClient\Utils\Manager as JsonApiClientManager;
use Cache\Adapter\Void\VoidCachePool;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for client
 */
interface ClientInterface
{
	/**
	 * Constructs the Client.
	 *
	 * @param array $options An array of options to set on the client.
	 *     Options include `api_version`, `api_domain`, `auth_domain`,
	 *     `cache_namespace`, `client_id`, `client_secret` and `redirect_url`.
	 * @param array $collaborators An array of collaborators that may be used to
	 *     override this provider's default behavior. Collaborators include
	 *     http_client`, `oauth2_provider`, `cache_provider`, `request_factory`
	 *     and `resource_factory`.
	 */
	public function __construct(array $options = [], array $collaborators = []);

	/**
	 * Get a cache item
	 *
	 * @param string $key The item key
	 *
	 * @return Psr\Cache\CacheItemInterface the cache item
	 */
	public function getCacheItem($key);

	/**
	 * Save a cache item
	 *
	 * @param Psr\Cache\CacheItemInterface $item The item
	 *
	 * @return void
	 */
	public function saveCacheItem(CacheItemInterface $item);

	/**
	 * Delete a cache item
	 *
	 * @param Psr\Cache\CacheItemInterface $item The item
	 *
	 * @return void
	 */
	public function deleteCacheItem(CacheItemInterface $item);

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
	 * Authorize the client credentials
	 *
	 * @param array $params for authorization code:
	 * [
	 *     'code' => 'authorization_code_from_callback_url...',
	 *     'state' => 'state_from_callback_url_for_csrf_protection',
	 * ]
	 *
	 * @throws InvalidArgumentException If a wrong state was set
	 * @throws MissingCredentialsException If no user or client credentials are set
	 * @throws UnauthorizedException contains the url to get an authorization code
	 *
	 * @return void
	 */
	public function authorize(array $params = []);

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
	 * @deprecated Will be set to private in future. Don't use it anymore
	 *
	 * @return Psr\Cache\CacheItemPoolInterface the cache provider
	 */
	public function getCacheProvider();

	/**
	 * Build a cache key
	 *
	 * @deprecated Will be set to private in future. Don't use it anymore
	 *
	 * @param string $key The key
	 * @return stirng The cache key
	 **/
	public function buildCacheKey($key);
}
