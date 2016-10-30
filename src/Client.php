<?php

namespace Youthweb\Api;

use Art4\JsonApiClient\Utils\Manager as JsonApiClientManager;
use Cache\Adapter\Void\VoidCachePool;
use DateInterval;
use DateTime;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Youthweb\Api\Exception\MissingCredentialsException;
use Youthweb\Api\Exception\UnauthorizedException;
use Youthweb\OAuth2\Client\Provider\Youthweb as Oauth2Provider;

/**
 * Simple PHP Youthweb client
 *
 * Website: http://github.com/youthweb/php-youthweb-api
 */
final class Client implements ClientInterface
{
	/**
	 * @var string
	 */
	private $api_version = '0.6';

	/**
	 * @var string
	 */
	private $api_domain = 'https://api.youthweb.net';

	/**
	 * @var string
	 */
	private $auth_domain = 'https://youthweb.net';

	/**
	 * @var array
	 */
	private $scope = [];

	/**
	 * @var HttpClientInterface
	 */
	private $http_client;

	/**
	 * @var League\OAuth2\Client\Provider\AbstractProvider
	 */
	private $oauth2_client;

	/**
	 * @var CacheItemPoolInterface
	 */
	private $cache_provider;

	/**
	 * @var string
	 */
	private $cache_namespace = 'php_youthweb_api.';

	/**
	 * @var array
	 */
	private $resources = [];

	/**
	 * @var RequestFactoryInterface
	 */
	private $request_factory;

	/**
	 * @var string
	 * @since Youthweb-API 0.6
	 */
	private $client_id;

	/**
	 * @var string
	 * @since Youthweb-API 0.6
	 */
	private $client_secret;

	/**
	 * @var string
	 * @since Youthweb-API 0.6
	 */
	private $redirect_url = '';

	/**
	 * @var string
	 * @deprecated Since Youthweb-API 0.6
	 */
	private $username = '';

	/**
	 * @var string
	 * @deprecated Since Youthweb-API 0.6
	 */
	private $token_secret = '';

	/**
	 * Constructs the Client.
	 *
	 * @param array $options An array of options to set on the client.
	 *     Options include `api_version`, `api_domain`, `auth_domain`,
	 *     `cache_namespace`, `client_id`, `client_secret`, `redirect_url` and `scope`.
	 * @param array $collaborators An array of collaborators that may be used to
	 *     override this provider's default behavior. Collaborators include
	 *     http_client`, `oauth2_provider`, `cache_provider`, `request_factory`
	 *     and `resource_factory`.
	 */
	public function __construct(array $options = [], array $collaborators = [])
	{
		$allowed_options = [
			'api_version',
			'api_domain',
			'auth_domain',
			'cache_namespace',
			'client_id',
			'client_secret',
			'redirect_url',
			'scope',
		];

		foreach ($options as $option => $value)
		{
			if ( in_array($option, $allowed_options) )
			{
				if ( $option !== 'scope' )
				{
					$value = strval($value);
				}
				// scope must be an array
				elseif ( ! is_array($value) )
				{
					$value = [strval($value)];
				}

				$this->{$option} = $value;
			}
		}

		if (empty($collaborators['http_client']))
		{
			$collaborators['http_client'] = new HttpClient(
				[
					// Guzzle config
				]
			);
		}

		$this->setHttpClient($collaborators['http_client']);

		if (empty($collaborators['oauth2_provider']))
		{
			$collaborators['oauth2_provider'] = new Oauth2Provider([
				'clientId'     => $this->client_id,
				'clientSecret' => $this->client_secret,
				'redirectUri'  => $this->redirect_url,
				'apiDomain'    => $this->api_domain,
				'domain'       => $this->auth_domain,
			]);
		}

		$this->setOauth2Provider($collaborators['oauth2_provider']);

		if (empty($collaborators['cache_provider']))
		{
			$collaborators['cache_provider'] = new VoidCachePool();
		}

		$this->setCacheProvider($collaborators['cache_provider']);

		if (empty($collaborators['request_factory']))
		{
			$collaborators['request_factory'] = new RequestFactory();
		}

		$this->setRequestFactory($collaborators['request_factory']);

		if (empty($collaborators['resource_factory']))
		{
			$collaborators['resource_factory'] = new ResourceFactory();
		}

		$this->setResourceFactory($collaborators['resource_factory']);
	}

	/**
	 * @param string $name
	 *
	 * @return Resource\AbstractResource
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getResource($name)
	{
		if ( ! isset($this->resources[$name]) )
		{
			$this->resources[$name] = $this->getResourceFactory()->createResource($name, $this);
		}

		return $this->resources[$name];
	}

	/**
	 * Get a cache item
	 *
	 * @param string $key The item key
	 *
	 * @return Psr\Cache\CacheItemInterface the cache item
	 */
	public function getCacheItem($key)
	{
		$key = $this->buildCacheKey($key);

		return $this->getCacheProvider()->getItem($key);
	}

	/**
	 * Save a cache item
	 *
	 * @param Psr\Cache\CacheItemInterface $item The item
	 *
	 * @return void
	 */
	public function saveCacheItem(CacheItemInterface $item)
	{
		$this->getCacheProvider()->saveDeferred($item);

		$this->getCacheProvider()->commit();
	}

	/**
	 * Delete a cache item
	 *
	 * @param Psr\Cache\CacheItemInterface $item The item
	 *
	 * @return void
	 */
	public function deleteCacheItem(CacheItemInterface $item)
	{
		$this->getCacheProvider()->deleteItem($item->getKey());
	}

	/**
	 * Returns the Url
	 *
	 * @deprecated Will be set to private in future. Don't use it anymore
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->api_domain;
	}

	/**
	 * Set the Url
	 *
	 * @deprecated Will be set to private in future. Use the constructor instead
	 *
	 * @param string $url The url
	 * @return self
	 */
	public function setUrl($url)
	{
		$this->api_domain = (string) $url;

		return $this;
	}

	/**
	 * Set the User Credentials
	 *
	 * @deprecated Since Youthweb-API 0.6
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
	 * @deprecated Since Youthweb-API 0.6
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
	 * Check if we have a access token
	 *
	 * @return boolean
	 */
	public function isAuthorized()
	{
		// Check the access token
		$access_token_item = $this->getCacheItem('access_token');

		if ( $access_token_item->isHit() )
		{
			return true;
		}

		$this->deleteCacheItem($access_token_item);

		// BC: Try to get a token with deprecated user token
		try
		{
			 $this->getResource('auth')->getBearerToken();

			 return true;
		}
		catch (\Exception $e) {}

		return false;
	}

	/**
	 * Force the Authorization with client (or user) credentials
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
	public function authorize(array $params = [])
	{
		// FIXME: client credentials could be already setted in the oauth2_provider
		if ( $this->client_id === null or $this->client_secret === null )
		{
			throw new MissingCredentialsException;
		}

		$state_item = $this->getCacheItem('state');

		if ( ! isset($params['code']) )
		{
			$options = [
				'scope' => $this->scope,
			];

			// If we don't have an authorization code then get one
			$auth_url = $this->getOauth2Provider()->getAuthorizationUrl($options);
			$state = $this->getOauth2Provider()->getState();
			$state_item->set($state);
			// Save state for 10 min
			$state_item->expiresAfter(new DateInterval('PT10M'));
			$this->saveCacheItem($state_item);

			throw UnauthorizedException::withAuthorizationUrl($auth_url, $state);
		}
		// Check state if present
		elseif ( isset($params['state']) )
		{
			if ( ! $state_item->isHit() or $state_item->get() !== $params['state'] )
			{
				$this->deleteCacheItem($state_item);

				throw new \InvalidArgumentException('Invalid state');
			}
		}

		$this->deleteCacheItem($state_item);

		// Try to get an access token (using the authorization code grant)
		$token = $this->getOauth2Provider()->getAccessToken('authorization_code', [
			'code' => $params['code'],
		]);

		$this->saveAccessToken($token);
	}

	/**
	 * Save a access token in cache provider
	 *
	 * @param AccessToken $token The access token
	 *
	 * @return void
	 */
	private function saveAccessToken(AccessToken $token)
	{
		$access_token_item = $this->getCacheItem('access_token');
		$access_token_item->set($token->getToken());
		$date = new DateTime('@'.$token->getExpires());
		$access_token_item->expiresAt($date);
		$this->saveCacheItem($access_token_item);
	}

	/**
	 * HTTP GETs a json $path and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @throws MissingCredentialsException If no user or client credentials are set
	 * @throws UnauthorizedException contains the url to get an authorization code
	 *
	 * @return array
	 */
	public function get($path, array $data = [])
	{
		$data['headers']['Authorization'] = $this->getBearerToken();

		$request = $this->createRequest('GET', $this->getUrl() . $path, $data);

		return $this->runRequest($request);
	}

	/**
	 * HTTP GETs a json $path without Authorization and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function getUnauthorized($path, array $data = [])
	{
		$request = $this->createRequest('GET', $this->getUrl() . $path, $data);

		return $this->runRequest($request);
	}

	/**
	 * HTTP POSTs a json $path without Authorization and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function postUnauthorized($path, array $data = [])
	{
		$request = $this->createRequest('POST', $this->getUrl() . $path, $data);

		return $this->runRequest($request);
	}

	/**
	 * Set a http client
	 *
	 * @deprecated Will be set to private in future. Use the constructor instead
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
	 * Set a oauth2 provider
	 *
	 * @param League\OAuth2\Client\Provider\AbstractProvider $oauth2_provider the oauth2 provider
	 * @return self
	 */
	private function setOauth2Provider(\League\OAuth2\Client\Provider\AbstractProvider $oauth2_provider)
	{
		$this->oauth2_provider = $oauth2_provider;

		return $this;
	}

	/**
	 * Get the oauth2 provider
	 *
	 * @return League\OAuth2\Client\Provider\AbstractProvider the oauth2 provider
	 */
	private function getOauth2Provider()
	{
		return $this->oauth2_provider;
	}

	/**
	 * Set a cache provider
	 *
	 * @deprecated Will be set to private in future. Use the constructor instead
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
	 * @deprecated Will be set to private in future. Don't use it anymore
	 *
	 * @return Psr\Cache\CacheItemPoolInterface the cache provider
	 */
	public function getCacheProvider()
	{
		return $this->cache_provider;
	}

	/**
	 * Build a cache key
	 *
	 * @deprecated Will be set to private in future. Don't use it anymore
	 *
	 * @param string $key The key
	 * @return stirng The cache key
	 **/
	public function buildCacheKey($key)
	{
		return $this->cache_namespace . strval($key);
	}

	/**
	 * Get the Bearer Token
	 *
	 * @throws MissingCredentialsException If no user or client credentials are set
	 * @throws UnauthorizedException contains the url to get an authorization code
	 *
	 * @return string The Bearer token incl. type e.g. "Bearer jcx45..."
	 */
	private function getBearerToken()
	{
		if ( ! $this->isAuthorized() )
		{
			// Throws an UnauthorizedException with auth_url and state
			$this->authorize();
		}

		$access_token_item = $this->getCacheItem('access_token');

		if ( ! $access_token_item->isHit() )
		{
			// BC: Try to get a token with deprecated user token
			return $this->getResource('auth')->getBearerToken();
		}

		throw new UnauthorizedException::withAuthorizationUrl(
			$this->getOauth2Provider()->getAuthorizationUrl(['scope' => $this->scope]),
			$this->getOauth2Provider()->getState(),
		);
	}

	/**
	 * @param RequestInterface $request The request to run
	 *
	 * @return mixed
	 *
	 * @throws \Exception If anything goes wrong on the request
	 */
	private function runRequest(RequestInterface $request)
	{
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
	 * Creates a PSR-7 request instance.
	 *
	 * @param  string $method
	 * @param  string $url
	 * @param  array $options
	 * @return RequestInterface
	 */
	private function createRequest($method, $url, array $options)
	{
		$options = $this->parseOptions($options);

		$default_headers = [
			'Content-Type' => 'application/vnd.api+json',
			'Accept' => 'application/vnd.api+json, application/vnd.api+json; net.youthweb.api.version=' . $this->api_version,
		];

		$headers = array_merge($default_headers, $options['headers']);

		return $this->getRequestFactory()->createRequest($method, $url, $headers, $options['body'], $options['version']);
	}

	/**
	 * Parses simplified options.
	 *
	 * @param array $options Simplified options.
	 *
	 * @return array Extended options for use with getRequest.
	 */
	private function parseOptions(array $options)
	{
		// Should match default values for getRequest
		$defaults = [
			'headers' => [],
			'body'    => null,
			'version' => '1.1',
		];

		return array_merge($defaults, $options);
	}

	/**
	 * @param ResponseInterface $response
	 *
	 * @return \Art4\JsonApiClient\Document
	 *
	 * @throws \Exception If anything goes wrong on the request
	 */
	private function parseResponse(ResponseInterface $response)
	{
		$body = $response->getBody()->getContents();

		return (new JsonApiClientManager())->parse($body);
	}

	/**
	 * Returns the http client
	 *
	 * @return HttpClientInterface The Http client
	 */
	private function getHttpClient()
	{
		return $this->http_client;
	}

	/**
	 * Set a request_factory
	 *
	 * @param RequestFactoryInterface $request_factory the request factory
	 * @return self
	 */
	private function setRequestFactory(RequestFactoryInterface $request_factory)
	{
		$this->request_factory = $request_factory;

		return $this;
	}

	/**
	 * Get the request factory
	 *
	 * @return RequestFactoryInterface the request factory
	 */
	private function getRequestFactory()
	{
		return $this->request_factory;
	}

	/**
	 * Set a resource_factory
	 *
	 * @param ResourceFactoryInterface $resource_factory the resource factory
	 * @return self
	 */
	private function setResourceFactory(ResourceFactoryInterface $resource_factory)
	{
		$this->resource_factory = $resource_factory;

		return $this;
	}

	/**
	 * Get the resource factory
	 *
	 * @return ResourceFactoryInterface the resource factory
	 */
	private function getResourceFactory()
	{
		return $this->resource_factory;
	}

	/**
	 * Handels a Exception from the Client
	 *
	 * @param \Exception $e The exception
	 * @return \Exception An exception for re-throwing
	 **/
	private function handleClientException(\Exception $e)
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
